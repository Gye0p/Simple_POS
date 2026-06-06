<?php
$pageTitle = 'Dashboard';
$adminOnly = true;

require_once __DIR__ . '/../includes/header.php';

$lowStockThreshold = (int) getSetting($conn, 'low_stock_threshold', '5');

$stmt = $conn->prepare(
    'SELECT COALESCE(SUM(total), 0) AS revenue, COUNT(*) AS transactions
     FROM sales
     WHERE DATE(created_at) = CURDATE()'
);
$stmt->execute();
$todaySales = $stmt->get_result()->fetch_assoc();
$stmt->close();

$totalProducts = (int) $conn->query(
    'SELECT COUNT(*) AS cnt FROM products WHERE is_active = 1'
)->fetch_assoc()['cnt'];

$stmt = $conn->prepare(
    'SELECT COUNT(*) AS cnt FROM products WHERE is_active = 1 AND stock < ?'
);
$stmt->bind_param('i', $lowStockThreshold);
$stmt->execute();
$lowStockCount = (int) $stmt->get_result()->fetch_assoc()['cnt'];
$stmt->close();

$stmt = $conn->prepare(
    'SELECT p.name, p.barcode, p.stock, p.unit, c.name AS category_name
     FROM products p
     LEFT JOIN categories c ON p.category_id = c.id
     WHERE p.is_active = 1 AND p.stock < ?
     ORDER BY p.stock ASC
     LIMIT 10'
);
$stmt->bind_param('i', $lowStockThreshold);
$stmt->execute();
$lowStockProducts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$itemsSoldToday = (int) $conn->query(
    'SELECT COALESCE(SUM(si.qty), 0) AS cnt
     FROM sale_items si
     INNER JOIN sales s ON si.sale_id = s.id
     WHERE DATE(s.created_at) = CURDATE()'
)->fetch_assoc()['cnt'];
?>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-stat-success">
                    <i class="bi bi-cash-stack"></i>
                </div>
                <div>
                    <div class="stat-value"><?= formatMoney((float) $todaySales['revenue']) ?></div>
                    <div class="stat-label">Today's Revenue</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-stat-primary">
                    <i class="bi bi-receipt-cutoff"></i>
                </div>
                <div>
                    <div class="stat-value"><?= (int) $todaySales['transactions'] ?></div>
                    <div class="stat-label">Transactions Today</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-stat-primary">
                    <i class="bi bi-box-seam"></i>
                </div>
                <div>
                    <div class="stat-value"><?= $totalProducts ?></div>
                    <div class="stat-label">Active Products</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-stat-warning">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
                <div>
                    <div class="stat-value"><?= $lowStockCount ?></div>
                    <div class="stat-label">Low Stock Items (&lt; <?= $lowStockThreshold ?>)</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-4">
        <div class="card stat-card h-100">
            <div class="card-body">
                <h6 class="text-muted mb-3">Today's Summary</h6>
                <div class="d-flex justify-content-between mb-2">
                    <span>Items Sold</span>
                    <strong><?= $itemsSoldToday ?></strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Transactions</span>
                    <strong><?= (int) $todaySales['transactions'] ?></strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Revenue</span>
                    <strong class="text-success"><?= formatMoney((float) $todaySales['revenue']) ?></strong>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card stat-card h-100">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="bi bi-exclamation-triangle text-warning"></i>
                    Low Stock Alerts
                </h6>
                <a href="<?= $base ?>/pages/products.php" class="btn btn-sm btn-outline-primary">Manage Products</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($lowStockProducts)): ?>
                    <div class="p-4 text-center text-muted">
                        <i class="bi bi-check-circle text-success fs-3"></i>
                        <p class="mb-0 mt-2">All products are sufficiently stocked.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Product</th>
                                    <th>Barcode</th>
                                    <th>Category</th>
                                    <th class="text-end">Stock</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($lowStockProducts as $product): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($product['name']) ?></td>
                                        <td><code><?= htmlspecialchars($product['barcode']) ?></code></td>
                                        <td><?= htmlspecialchars($product['category_name'] ?? '—') ?></td>
                                        <td class="text-end">
                                            <span class="badge bg-danger">
                                                <?= (int) $product['stock'] ?> <?= htmlspecialchars($product['unit']) ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
