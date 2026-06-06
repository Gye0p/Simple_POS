<?php
session_start();

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdminAjax();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
}

$id = (int) ($_POST['id'] ?? 0);

if ($id <= 0) {
    jsonResponse(['success' => false, 'message' => 'Invalid category ID']);
}

$stmt = $conn->prepare('SELECT COUNT(*) AS cnt FROM products WHERE category_id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$count = (int) $stmt->get_result()->fetch_assoc()['cnt'];
$stmt->close();

if ($count > 0) {
    jsonResponse(['success' => false, 'message' => 'Cannot delete category with ' . $count . ' product(s). Reassign products first.']);
}

$stmt = $conn->prepare('DELETE FROM categories WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$affected = $stmt->affected_rows;
$stmt->close();

if ($affected === 0) {
    jsonResponse(['success' => false, 'message' => 'Category not found']);
}

jsonResponse(['success' => true, 'message' => 'Category deleted']);
