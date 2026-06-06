<?php
session_start();

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    die('Unauthorized');
}

$saleId = (int) ($_GET['sale_id'] ?? 0);

if ($saleId <= 0) {
    die('Invalid sale ID');
}

$stmt = $conn->prepare(
    'SELECT s.*, u.name AS cashier_name
     FROM sales s
     LEFT JOIN users u ON s.cashier_id = u.id
     WHERE s.id = ?
     LIMIT 1'
);
$stmt->bind_param('i', $saleId);
$stmt->execute();
$sale = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$sale) {
    die('Sale not found');
}

$stmt = $conn->prepare(
    'SELECT si.qty, si.unit_price, si.subtotal, p.name, p.unit
     FROM sale_items si
     INNER JOIN products p ON si.product_id = p.id
     WHERE si.sale_id = ?
     ORDER BY si.id ASC'
);
$stmt->bind_param('i', $saleId);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$storeName = getSetting($conn, 'store_name', 'My Convenience Store');
$storeAddress = getSetting($conn, 'store_address', '');
$storeTin = getSetting($conn, 'store_tin', '');
$receiptFooter = getSetting($conn, 'receipt_footer', 'Thank you for shopping with us!');
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Receipt <?= htmlspecialchars($sale['or_number']) ?></title>
    <link href="../assets/css/receipt.css" rel="stylesheet">
</head>
<body>
    <div class="receipt" id="receipt">
        <div class="receipt-header text-center">
            <h2 class="store-name"><?= htmlspecialchars($storeName) ?></h2>
            <?php if ($storeAddress): ?>
                <p class="store-address"><?= htmlspecialchars($storeAddress) ?></p>
            <?php endif; ?>
            <?php if ($storeTin): ?>
                <p class="store-tin">TIN: <?= htmlspecialchars($storeTin) ?></p>
            <?php endif; ?>
        </div>

        <div class="receipt-meta">
            <p>OR #: <strong><?= htmlspecialchars($sale['or_number']) ?></strong></p>
            <p>Date: <?= date('M d, Y h:i A', strtotime($sale['created_at'])) ?></p>
            <p>Cashier: <?= htmlspecialchars($sale['cashier_name'] ?? 'N/A') ?></p>
        </div>

        <hr>

        <table class="receipt-items">
            <thead>
                <tr>
                    <th>Item</th>
                    <th class="text-right">Qty</th>
                    <th class="text-right">Price</th>
                    <th class="text-right">Amt</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['name']) ?></td>
                        <td class="text-right"><?= (int) $item['qty'] ?></td>
                        <td class="text-right"><?= number_format((float) $item['unit_price'], 2) ?></td>
                        <td class="text-right"><?= number_format((float) $item['subtotal'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <hr>

        <div class="receipt-totals">
            <div class="total-row">
                <span>Subtotal</span>
                <span>₱<?= number_format((float) $sale['subtotal'], 2) ?></span>
            </div>
            <?php if ((float) $sale['discount'] > 0): ?>
                <div class="total-row">
                    <span>Discount</span>
                    <span>-₱<?= number_format((float) $sale['discount'], 2) ?></span>
                </div>
            <?php endif; ?>
            <div class="total-row grand-total">
                <span>TOTAL</span>
                <span>₱<?= number_format((float) $sale['total'], 2) ?></span>
            </div>
            <div class="total-row">
                <span>Cash Tendered</span>
                <span>₱<?= number_format((float) $sale['cash_tendered'], 2) ?></span>
            </div>
            <div class="total-row">
                <span>Change</span>
                <span>₱<?= number_format((float) $sale['change_amount'], 2) ?></span>
            </div>
        </div>

        <hr>

        <p class="receipt-footer text-center"><?= htmlspecialchars($receiptFooter) ?></p>
    </div>
</body>
</html>
