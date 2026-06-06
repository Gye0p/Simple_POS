<?php
session_start();

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdminAjax();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
}

$id = (int) ($_POST['id'] ?? 0);
$name = trim($_POST['name'] ?? '');

if ($name === '') {
    jsonResponse(['success' => false, 'message' => 'Category name is required']);
}

if ($id > 0) {
    $stmt = $conn->prepare('UPDATE categories SET name = ? WHERE id = ?');
    $stmt->bind_param('si', $name, $id);
} else {
    $stmt = $conn->prepare('INSERT INTO categories (name) VALUES (?)');
    $stmt->bind_param('s', $name);
}

$stmt->execute();
$stmt->close();

jsonResponse(['success' => true, 'message' => $id > 0 ? 'Category updated' : 'Category added']);
