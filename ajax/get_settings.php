<?php
session_start();

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
}

jsonResponse([
    'success' => true,
    'settings' => [
        'store_name'          => getSetting($conn, 'store_name', 'My Convenience Store'),
        'store_address'       => getSetting($conn, 'store_address', ''),
        'store_tin'           => getSetting($conn, 'store_tin', ''),
        'receipt_footer'      => getSetting($conn, 'receipt_footer', 'Thank you for shopping with us!'),
        'low_stock_threshold' => (int) getSetting($conn, 'low_stock_threshold', '5'),
    ],
]);
