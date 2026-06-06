<?php
session_start();

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
}

$input = json_decode(file_get_contents('php://input'), true);

if (!is_array($input)) {
    jsonResponse(['success' => false, 'message' => 'Invalid request data']);
}

$items = $input['items'] ?? [];
$discountType = $input['discount_type'] ?? 'amount';
$discountValue = (float) ($input['discount_value'] ?? 0);
$cashTendered = (float) ($input['cash_tendered'] ?? 0);

if (empty($items)) {
    jsonResponse(['success' => false, 'message' => 'Cart is empty']);
}

$subtotal = 0.0;
$validatedItems = [];

foreach ($items as $item) {
    $productId = (int) ($item['id'] ?? 0);
    $qty = (int) ($item['qty'] ?? 0);
    $unitPrice = (float) ($item['price'] ?? 0);

    if ($productId <= 0 || $qty <= 0) {
        jsonResponse(['success' => false, 'message' => 'Invalid cart item']);
    }

    $stmt = $conn->prepare(
        'SELECT id, name, price, stock, is_active FROM products WHERE id = ? LIMIT 1'
    );
    $stmt->bind_param('i', $productId);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$product || !(int) $product['is_active']) {
        jsonResponse(['success' => false, 'message' => 'Product no longer available: ID ' . $productId]);
    }

    if ((int) $product['stock'] < $qty) {
        jsonResponse([
            'success' => false,
            'message' => 'Insufficient stock for ' . $product['name'] . '. Available: ' . $product['stock'],
        ]);
    }

    $lineSubtotal = round($unitPrice * $qty, 2);
    $subtotal += $lineSubtotal;

    $validatedItems[] = [
        'product_id'  => $productId,
        'qty'         => $qty,
        'unit_price'  => $unitPrice,
        'subtotal'    => $lineSubtotal,
    ];
}

$subtotal = round($subtotal, 2);

if ($discountType === 'percent') {
    $discount = round($subtotal * min($discountValue, 100) / 100, 2);
} else {
    $discount = round(min($discountValue, $subtotal), 2);
}

$vat = 0.0;
$total = round($subtotal - $discount, 2);

if ($cashTendered < $total) {
    jsonResponse(['success' => false, 'message' => 'Cash tendered is less than the total amount']);
}

$changeAmount = round($cashTendered - $total, 2);
$orNumber = generateOrNumber($conn);
$cashierId = (int) $_SESSION['user_id'];

$conn->begin_transaction();

try {
    $stmt = $conn->prepare(
        'INSERT INTO sales (or_number, cashier_id, subtotal, discount, vat, total, cash_tendered, change_amount)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->bind_param(
        'sidddddd',
        $orNumber,
        $cashierId,
        $subtotal,
        $discount,
        $vat,
        $total,
        $cashTendered,
        $changeAmount
    );
    $stmt->execute();
    $saleId = (int) $conn->insert_id;
    $stmt->close();

    $itemStmt = $conn->prepare(
        'INSERT INTO sale_items (sale_id, product_id, qty, unit_price, subtotal)
         VALUES (?, ?, ?, ?, ?)'
    );

    $stockStmt = $conn->prepare(
        'UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?'
    );

    foreach ($validatedItems as $item) {
        $itemStmt->bind_param(
            'iiidd',
            $saleId,
            $item['product_id'],
            $item['qty'],
            $item['unit_price'],
            $item['subtotal']
        );
        $itemStmt->execute();

        $stockStmt->bind_param(
            'iii',
            $item['qty'],
            $item['product_id'],
            $item['qty']
        );
        $stockStmt->execute();

        if ($stockStmt->affected_rows === 0) {
            throw new Exception('Stock update failed for product ID ' . $item['product_id']);
        }
    }

    $itemStmt->close();
    $stockStmt->close();

    $conn->commit();

    jsonResponse([
        'success'   => true,
        'sale_id'   => $saleId,
        'or_number' => $orNumber,
        'total'     => $total,
        'change'    => $changeAmount,
    ]);
} catch (Exception $e) {
    $conn->rollback();
    jsonResponse(['success' => false, 'message' => 'Checkout failed: ' . $e->getMessage()]);
}
