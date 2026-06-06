<?php
session_start();

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
}

$barcode = trim($_GET['barcode'] ?? $_POST['barcode'] ?? '');

if ($barcode === '') {
    jsonResponse(['success' => false, 'message' => 'Barcode is required']);
}

$lowStockThreshold = (int) getSetting($conn, 'low_stock_threshold', '5');

$stmt = $conn->prepare(
    'SELECT p.id, p.barcode, p.name, p.price, p.stock, p.unit, p.is_active,
            c.name AS category_name
     FROM products p
     LEFT JOIN categories c ON p.category_id = c.id
     WHERE p.barcode = ?
     LIMIT 1'
);
$stmt->bind_param('s', $barcode);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();
$stmt->close();

if (!$product) {
    jsonResponse(['success' => false, 'message' => 'Product not found for barcode: ' . $barcode]);
}

if (!(int) $product['is_active']) {
    jsonResponse(['success' => false, 'message' => 'This product is inactive']);
}

if ((int) $product['stock'] <= 0) {
    jsonResponse(['success' => false, 'message' => 'Product is out of stock']);
}

jsonResponse([
    'success' => true,
    'product' => [
        'id'            => (int) $product['id'],
        'barcode'       => $product['barcode'],
        'name'          => $product['name'],
        'price'         => (float) $product['price'],
        'stock'         => (int) $product['stock'],
        'unit'          => $product['unit'],
        'category_name' => $product['category_name'],
        'low_stock'     => (int) $product['stock'] < $lowStockThreshold,
    ],
]);
