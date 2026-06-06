<?php
session_start();

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdminAjax();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
}

$input = json_decode(file_get_contents('php://input'), true);

if (!is_array($input) || empty($input['items'])) {
    jsonResponse(['success' => false, 'message' => 'No stock adjustments provided']);
}

$stmt = $conn->prepare('UPDATE products SET stock = stock + ? WHERE id = ?');

foreach ($input['items'] as $item) {
    $productId = (int) ($item['id'] ?? 0);
    $adjustment = (int) ($item['adjustment'] ?? 0);

    if ($productId <= 0 || $adjustment === 0) {
        continue;
    }

    $stmt->bind_param('ii', $adjustment, $productId);
    $stmt->execute();
}

$stmt->close();

jsonResponse(['success' => true, 'message' => 'Stock updated']);
