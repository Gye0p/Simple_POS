<?php
$pageTitle = 'Reports';
$adminOnly = true;

require_once __DIR__ . '/../includes/header.php';

$dateFrom = $_GET['date_from'] ?? date('Y-m-01');
$dateTo = $_GET['date_to'] ?? date('Y-m-d');
$lowStockThreshold = (int) getSetting($conn, 'low_stock_threshold', '5');

$stmt = $conn->prepare(
    'SELECT COUNT(*) AS transactions, COALESCE(SUM(total), 0) AS revenue
     FROM sales WHERE DATE(created_at) BETWEEN ? AND ?'
);
$stmt->bind_param('ss', $dateFrom, $dateTo);
$stmt->execute();
$summary = $stmt->get_result()->fetch_assoc();
$stmt->close();

$stmt = $conn->prepare(
    'SELECT COALESCE(SUM(si.qty), 0) AS items_sold
     FROM sale_items si
     INNER JOIN sales s ON si.sale_id = s.id
     WHERE DATE(s.created_at) BETWEEN ? AND ?'
);
$stmt->bind_param('ss', $dateFrom, $dateTo);
$stmt->execute();
$itemsSold = (int) $stmt->get_result()->fetch_assoc()['items_sold'];
$stmt->close();

$stmt = $conn->prepare(
    'SELECT p.name, SUM(si.qty) AS qty_sold, SUM(si.subtotal) AS revenue
     FROM sale_items si
     INNER JOIN sales s ON si.sale_id = s.id
     INNER JOIN products p ON si.product_id = p.id
     WHERE DATE(s.created_at) BETWEEN ? AND ?
     GROUP BY p.id, p.name
     ORDER BY qty_sold DESC
     LIMIT 20'
);
$stmt->bind_param('ss', $dateFrom, $dateTo);
$stmt->execute();
$topProducts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$stmt = $conn->prepare(
    'SELECT p.name, p.barcode, p.stock, p.unit, c.name AS category_name
     FROM products p
     LEFT JOIN categories c ON p.category_id = c.id
     WHERE p.is_active = 1 AND p.stock < ?
     ORDER BY p.stock ASC'
);
$stmt->bind_param('i', $lowStockThreshold);
$stmt->execute();
$lowStock = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$stmt = $conn->prepare(
    'SELECT u.name AS cashier_name, COUNT(s.id) AS transactions, COALESCE(SUM(s.total), 0) AS revenue
     FROM sales s
     INNER JOIN users u ON s.cashier_id = u.id
     WHERE DATE(s.created_at) BETWEEN ? AND ?
     GROUP BY u.id, u.name
     ORDER BY revenue DESC'
);
$stmt->bind_param('ss', $dateFrom, $dateTo);
$stmt->execute();
$cashierPerf = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<div class="card stat-card mb-3 no-print">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label">From</label>
                <input type="date" class="form-control" name="date_from" value="<?= htmlspecialchars($dateFrom) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">To</label>
                <input type="date" class="form-control" name="date_to" value="<?= htmlspecialchars($dateTo) ?>">
            </div>
            <div class="col-md-6 d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-funnel"></i> Apply</button>
                <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
                    <i class="bi bi-printer"></i> Print Report
                </button>
            </div>
        </form>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card stat-card">
            <div class="card-body text-center">
                <div class="stat-value"><?= (int) $summary['transactions'] ?></div>
                <div class="stat-label">Total Transactions</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card">
            <div class="card-body text-center">
                <div class="stat-value"><?= formatMoney((float) $summary['revenue']) ?></div>
                <div class="stat-label">Total Revenue</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card">
            <div class="card-body text-center">
                <div class="stat-value"><?= $itemsSold ?></div>
                <div class="stat-label">Items Sold</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="card stat-card">
            <div class="card-header bg-white d-flex justify-content-between">
                <h6 class="mb-0">Top Selling Products</h6>
                <button class="btn btn-sm btn-outline-secondary no-print" onclick="exportTable('topProductsTable', 'top_products')">CSV</button>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0" id="topProductsTable">
                    <thead class="table-light">
                        <tr><th>#</th><th>Product</th><th class="text-end">Qty</th><th class="text-end">Revenue</th></tr>
                    </thead>
                    <tbody>
                        <?php if (empty($topProducts)): ?>
                            <tr><td colspan="4" class="text-center text-muted py-4">No sales in this period</td></tr>
                        <?php else: ?>
                            <?php foreach ($topProducts as $i => $p): ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td><?= htmlspecialchars($p['name']) ?></td>
                                    <td class="text-end"><?= (int) $p['qty_sold'] ?></td>
                                    <td class="text-end"><?= formatMoney((float) $p['revenue']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card stat-card">
            <div class="card-header bg-white d-flex justify-content-between">
                <h6 class="mb-0">Per-Cashier Performance</h6>
                <button class="btn btn-sm btn-outline-secondary no-print" onclick="exportTable('cashierTable', 'cashier_performance')">CSV</button>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0" id="cashierTable">
                    <thead class="table-light">
                        <tr><th>Cashier</th><th class="text-end">Transactions</th><th class="text-end">Revenue</th></tr>
                    </thead>
                    <tbody>
                        <?php if (empty($cashierPerf)): ?>
                            <tr><td colspan="3" class="text-center text-muted py-4">No data</td></tr>
                        <?php else: ?>
                            <?php foreach ($cashierPerf as $c): ?>
                                <tr>
                                    <td><?= htmlspecialchars($c['cashier_name']) ?></td>
                                    <td class="text-end"><?= (int) $c['transactions'] ?></td>
                                    <td class="text-end"><?= formatMoney((float) $c['revenue']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-12">
        <div class="card stat-card">
            <div class="card-header bg-white d-flex justify-content-between">
                <h6 class="mb-0">Low Stock Report (&lt; <?= $lowStockThreshold ?>)</h6>
                <button class="btn btn-sm btn-outline-secondary no-print" onclick="exportTable('lowStockTable', 'low_stock')">CSV</button>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0" id="lowStockTable">
                    <thead class="table-light">
                        <tr><th>Product</th><th>Barcode</th><th>Category</th><th class="text-end">Stock</th></tr>
                    </thead>
                    <tbody>
                        <?php if (empty($lowStock)): ?>
                            <tr><td colspan="4" class="text-center text-muted py-4">All products sufficiently stocked</td></tr>
                        <?php else: ?>
                            <?php foreach ($lowStock as $p): ?>
                                <tr>
                                    <td><?= htmlspecialchars($p['name']) ?></td>
                                    <td><code><?= htmlspecialchars($p['barcode']) ?></code></td>
                                    <td><?= htmlspecialchars($p['category_name'] ?? '—') ?></td>
                                    <td class="text-end"><span class="badge bg-danger"><?= (int) $p['stock'] ?> <?= htmlspecialchars($p['unit']) ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function exportTable(tableId, filename) {
    const table = document.getElementById(tableId);
    let csv = [];
    table.querySelectorAll('tr').forEach(row => {
        const cols = [];
        row.querySelectorAll('th, td').forEach(cell => {
            cols.push('"' + cell.innerText.replace(/"/g, '""').trim() + '"');
        });
        if (cols.length) csv.push(cols.join(','));
    });
    const blob = new Blob([csv.join('\n')], { type: 'text/csv' });
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = filename + '.csv';
    a.click();
}
</script>

<style>
@media print {
    .sidebar, .topbar, .no-print, .btn { display: none !important; }
    #page-content { margin: 0 !important; }
    .main-content { padding: 0 !important; }
}
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
