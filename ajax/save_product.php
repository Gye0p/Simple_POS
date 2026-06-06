<?php
session_start();

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdminAjax();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
}

$id = (int) ($_POST['id'] ?? 0);
$barcode = trim($_POST['barcode'] ?? '');
$name = trim($_POST['name'] ?? '');
$categoryId = (int) ($_POST['category_id'] ?? 0);
$price = (float) ($_POST['price'] ?? 0);
$costPrice = (float) ($_POST['cost_price'] ?? 0);
$stock = (int) ($_POST['stock'] ?? 0);
$unit = trim($_POST['unit'] ?? 'pcs');
$isActive = (int) ($_POST['is_active'] ?? 1);

if ($barcode === '' || $name === '') {
    jsonResponse(['success' => false, 'message' => 'Barcode and name are required']);
}

if ($price < 0) {
    jsonResponse(['success' => false, 'message' => 'Price must be zero or greater']);
}

try {
    if ($id > 0) {
        if ($categoryId > 0) {
            $stmt = $conn->prepare(
                'UPDATE products
                 SET barcode = ?, name = ?, category_id = ?, price = ?, cost_price = ?, stock = ?, unit = ?, is_active = ?
                 WHERE id = ?'
            );
            $stmt->bind_param('ssiddisii', $barcode, $name, $categoryId, $price, $costPrice, $stock, $unit, $isActive, $id);
        } else {
            $stmt = $conn->prepare(
                'UPDATE products
                 SET barcode = ?, name = ?, category_id = NULL, price = ?, cost_price = ?, stock = ?, unit = ?, is_active = ?
                 WHERE id = ?'
            );
            $stmt->bind_param('ssddisii', $barcode, $name, $price, $costPrice, $stock, $unit, $isActive, $id);
        }
    } elseif ($categoryId > 0) {
        $stmt = $conn->prepare(
            'INSERT INTO products (barcode, name, category_id, price, cost_price, stock, unit, is_active)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->bind_param('ssiddisi', $barcode, $name, $categoryId, $price, $costPrice, $stock, $unit, $isActive);
    } else {
        $stmt = $conn->prepare(
            'INSERT INTO products (barcode, name, category_id, price, cost_price, stock, unit, is_active)
             VALUES (?, ?, NULL, ?, ?, ?, ?, ?)'
        );
        $stmt->bind_param('ssddisi', $barcode, $name, $price, $costPrice, $stock, $unit, $isActive);
    }

    $stmt->execute();
    $stmt->close();
    jsonResponse(['success' => true, 'message' => $id > 0 ? 'Product updated' : 'Product added']);
} catch (mysqli_sql_exception $e) {
    if (isset($stmt)) {
        $stmt->close();
    }
    if (str_contains($e->getMessage(), 'Duplicate')) {
        jsonResponse(['success' => false, 'message' => 'Barcode already exists']);
    }
    jsonResponse(['success' => false, 'message' => 'Failed to save product']);
}
