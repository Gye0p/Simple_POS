<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/functions.php';

requireLogin();

if (!empty($adminOnly)) {
    requireAdmin();
}

$pageTitle = $pageTitle ?? 'POS System';
$base = baseUrl();
$isAdmin = $_SESSION['role'] === 'admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> — POS System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="<?= $base ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>
    <div class="d-flex" id="wrapper">
        <nav id="sidebar" class="sidebar">
            <div class="sidebar-brand">
                <i class="bi bi-shop"></i>
                <span>Store POS</span>
            </div>

            <ul class="sidebar-nav">
                <?php if ($isAdmin): ?>
                    <li>
                        <a href="<?= $base ?>/pages/dashboard.php"
                           class="<?= isActivePage('dashboard.php') ? 'active' : '' ?>">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="<?= $base ?>/pages/cashier.php"
                           class="<?= isActivePage('cashier.php') ? 'active' : '' ?>">
                            <i class="bi bi-cart3"></i> Cashier
                        </a>
                    </li>
                    <li>
                        <a href="<?= $base ?>/pages/products.php"
                           class="<?= isActivePage('products.php') ? 'active' : '' ?>">
                            <i class="bi bi-box-seam"></i> Products
                        </a>
                    </li>
                    <li>
                        <a href="<?= $base ?>/pages/categories.php"
                           class="<?= isActivePage('categories.php') ? 'active' : '' ?>">
                            <i class="bi bi-tags"></i> Categories
                        </a>
                    </li>
                    <li>
                        <a href="<?= $base ?>/pages/users.php"
                           class="<?= isActivePage('users.php') ? 'active' : '' ?>">
                            <i class="bi bi-people"></i> Users
                        </a>
                    </li>
                    <li>
                        <a href="<?= $base ?>/pages/sales.php"
                           class="<?= isActivePage('sales.php') ? 'active' : '' ?>">
                            <i class="bi bi-receipt"></i> Sales
                        </a>
                    </li>
                    <li>
                        <a href="<?= $base ?>/pages/reports.php"
                           class="<?= isActivePage('reports.php') ? 'active' : '' ?>">
                            <i class="bi bi-bar-chart-line"></i> Reports
                        </a>
                    </li>
                    <li>
                        <a href="<?= $base ?>/pages/settings.php"
                           class="<?= isActivePage('settings.php') ? 'active' : '' ?>">
                            <i class="bi bi-gear"></i> Settings
                        </a>
                    </li>
                <?php else: ?>
                    <li>
                        <a href="<?= $base ?>/pages/cashier.php"
                           class="<?= isActivePage('cashier.php') ? 'active' : '' ?>">
                            <i class="bi bi-cart3"></i> Cashier
                        </a>
                    </li>
                <?php endif; ?>
            </ul>

            <div class="sidebar-footer">
                <div class="user-info">
                    <i class="bi bi-person-circle"></i>
                    <div>
                        <div class="user-name"><?= htmlspecialchars($_SESSION['name']) ?></div>
                        <small class="user-role"><?= htmlspecialchars(ucfirst($_SESSION['role'])) ?></small>
                    </div>
                </div>
                <a href="<?= $base ?>/auth/logout.php" class="btn btn-outline-light btn-sm w-100 mt-2">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </a>
            </div>
        </nav>

        <div id="page-content" class="flex-grow-1">
            <header class="topbar">
                <h1 class="page-heading mb-0"><?= htmlspecialchars($pageTitle) ?></h1>
            </header>
            <main class="main-content">
