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
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$role = $_POST['role'] ?? 'cashier';
$isActive = (int) ($_POST['is_active'] ?? 1);

if ($name === '' || $username === '') {
    jsonResponse(['success' => false, 'message' => 'Name and username are required']);
}

if (!in_array($role, ['admin', 'cashier'], true)) {
    jsonResponse(['success' => false, 'message' => 'Invalid role']);
}

if ($id > 0) {
    if ($password !== '') {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare(
            'UPDATE users SET name = ?, username = ?, password = ?, role = ?, is_active = ? WHERE id = ?'
        );
        $stmt->bind_param('ssssii', $name, $username, $hash, $role, $isActive, $id);
    } else {
        $stmt = $conn->prepare(
            'UPDATE users SET name = ?, username = ?, role = ?, is_active = ? WHERE id = ?'
        );
        $stmt->bind_param('sssii', $name, $username, $role, $isActive, $id);
    }
} else {
    if ($password === '') {
        jsonResponse(['success' => false, 'message' => 'Password is required for new users']);
    }
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare(
        'INSERT INTO users (name, username, password, role, is_active) VALUES (?, ?, ?, ?, ?)'
    );
    $stmt->bind_param('ssssi', $name, $username, $hash, $role, $isActive);
}

try {
    $stmt->execute();
    $stmt->close();
    jsonResponse(['success' => true, 'message' => $id > 0 ? 'User updated' : 'User added']);
} catch (mysqli_sql_exception $e) {
    if (isset($stmt)) {
        $stmt->close();
    }
    if (str_contains($e->getMessage(), 'Duplicate')) {
        jsonResponse(['success' => false, 'message' => 'Username already exists']);
    }
    jsonResponse(['success' => false, 'message' => 'Failed to save user']);
}
