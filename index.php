<?php
session_start();

if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: pages/dashboard.php');
    } else {
        header('Location: pages/cashier.php');
    }
} else {
    header('Location: auth/login.php');
}

exit;
