<?php

function baseUrl(): string
{
    static $url = null;

    if ($url === null) {
        $url = dirname(dirname($_SERVER['SCRIPT_NAME']));
        if ($url === '/' || $url === '\\') {
            $url = '';
        }
    }

    return $url;
}

function requireLogin(): void
{
    if (!isset($_SESSION['user_id'])) {
        header('Location: ' . baseUrl() . '/auth/login.php');
        exit;
    }
}

function requireAdmin(): void
{
    requireLogin();

    if ($_SESSION['role'] !== 'admin') {
        header('Location: ' . baseUrl() . '/pages/cashier.php');
        exit;
    }
}

function getSetting(mysqli $conn, string $key, string $default = ''): string
{
    $stmt = $conn->prepare('SELECT setting_value FROM settings WHERE setting_key = ? LIMIT 1');
    $stmt->bind_param('s', $key);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    return $row ? (string) $row['setting_value'] : $default;
}

function formatMoney(float $amount): string
{
    return '₱' . number_format($amount, 2);
}

function isActivePage(string $page): bool
{
    return basename($_SERVER['SCRIPT_NAME']) === $page;
}

function generateOrNumber(mysqli $conn): string
{
    $datePart = date('Ymd');
    $prefix = 'OR-' . $datePart . '-';

    $stmt = $conn->prepare(
        'SELECT or_number FROM sales WHERE or_number LIKE ? ORDER BY or_number DESC LIMIT 1'
    );
    $like = $prefix . '%';
    $stmt->bind_param('s', $like);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    $sequence = 1;
    if ($row) {
        $lastPart = substr($row['or_number'], -4);
        $sequence = (int) $lastPart + 1;
    }

    return $prefix . str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
}

function jsonResponse(array $data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function requireAdminAjax(): void
{
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
    }
}

function setSetting(mysqli $conn, string $key, string $value): bool
{
    $stmt = $conn->prepare(
        'INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
         ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
    );
    $stmt->bind_param('ss', $key, $value);
    $ok = $stmt->execute();
    $stmt->close();

    return $ok;
}
