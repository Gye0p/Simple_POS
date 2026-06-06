<?php
session_start();

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdminAjax();

$categoryId = (int) ($_GET['category_id'] ?? 0);
$lowStockThreshold = (int) getSetting($conn, 'low_stock_threshold', '5');

$sql = 'SELECT p.id, p.barcode, p.name, p.category_id, p.price, p.cost_price, p.stock, p.unit, p.is_active,
               c.name AS category_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id';

if ($categoryId > 0) {
    $sql .= ' WHERE p.category_id = ?';
}

$sql .= ' ORDER BY p.name ASC';

$stmt = $conn->prepare($sql);

if ($categoryId > 0) {
    $stmt->bind_param('i', $categoryId);
}

$stmt->execute();
$result = $stmt->get_result();
$products = [];

while ($row = $result->fetch_assoc()) {
    $row['id'] = (int) $row['id'];
    $row['category_id'] = $row['category_id'] ? (int) $row['category_id'] : null;
    $row['price'] = (float) $row['price'];
    $row['cost_price'] = (float) $row['cost_price'];
    $row['stock'] = (int) $row['stock'];
    $row['is_active'] = (int) $row['is_active'];
    $row['low_stock'] = $row['stock'] < $lowStockThreshold;
    $products[] = $row;
}

$stmt->close();

jsonResponse(['success' => true, 'products' => $products]);
