<?php
session_start();

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdminAjax();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
}

$id = (int) ($_POST['id'] ?? 0);
$isActive = (int) ($_POST['is_active'] ?? 0);

if ($id <= 0) {
    jsonResponse(['success' => false, 'message' => 'Invalid user ID']);
}

if ($id === (int) $_SESSION['user_id'] && $isActive === 0) {
    jsonResponse(['success' => false, 'message' => 'You cannot deactivate your own account']);
}

$stmt = $conn->prepare('UPDATE users SET is_active = ? WHERE id = ?');
$stmt->bind_param('ii', $isActive, $id);
$stmt->execute();
$affected = $stmt->affected_rows;
$stmt->close();

if ($affected === 0) {
    jsonResponse(['success' => false, 'message' => 'User not found']);
}

jsonResponse([
    'success' => true,
    'message' => $isActive ? 'User activated' : 'User deactivated',
]);
