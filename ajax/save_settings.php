<?php
session_start();

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdminAjax();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
}

$settings = [
    'store_name'          => trim($_POST['store_name'] ?? ''),
    'store_address'       => trim($_POST['store_address'] ?? ''),
    'store_tin'           => trim($_POST['store_tin'] ?? ''),
    'receipt_footer'      => trim($_POST['receipt_footer'] ?? ''),
    'low_stock_threshold' => (string) max(1, (int) ($_POST['low_stock_threshold'] ?? 5)),
];

if ($settings['store_name'] === '') {
    jsonResponse(['success' => false, 'message' => 'Store name is required']);
}

foreach ($settings as $key => $value) {
    setSetting($conn, $key, $value);
}

jsonResponse(['success' => true, 'message' => 'Settings saved']);
