<?php
$pageTitle = 'Sales History';
$adminOnly = true;

require_once __DIR__ . '/../includes/header.php';

$dateFrom = $_GET['date_from'] ?? date('Y-m-d');
$dateTo = $_GET['date_to'] ?? date('Y-m-d');

$stmt = $conn->prepare(
    'SELECT s.id, s.or_number, s.total, s.discount, s.cash_tendered, s.change_amount, s.created_at,
            u.name AS cashier_name,
            (SELECT COALESCE(SUM(qty), 0) FROM sale_items WHERE sale_id = s.id) AS item_count
     FROM sales s
     LEFT JOIN users u ON s.cashier_id = u.id
     WHERE DATE(s.created_at) BETWEEN ? AND ?
     ORDER BY s.created_at DESC'
);
$stmt->bind_param('ss', $dateFrom, $dateTo);
$stmt->execute();
$sales = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$totalRevenue = array_sum(array_column($sales, 'total'));
$totalTransactions = count($sales);
?>

<div class="card stat-card mb-3">
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
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-funnel"></i> Filter
                </button>
            </div>
            <div class="col-md-4 text-md-end">
                <span class="badge bg-success fs-6 me-2"><?= $totalTransactions ?> transactions</span>
                <span class="badge bg-primary fs-6"><?= formatMoney((float) $totalRevenue) ?> total</span>
            </div>
        </form>
    </div>
</div>

<div class="card stat-card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Sales Records</h6>
        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="exportTable('salesTable', 'sales_history')">
            <i class="bi bi-download"></i> Export CSV
        </button>
    </div>
    <div class="card-body">
        <table class="table table-hover datatable" id="salesTable">
            <thead class="table-light">
                <tr>
                    <th>Date & Time</th>
                    <th>OR Number</th>
                    <th>Cashier</th>
                    <th class="text-center">Items</th>
                    <th class="text-end">Discount</th>
                    <th class="text-end">Total</th>
                    <th class="text-center">Receipt</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sales as $sale): ?>
                    <tr>
                        <td><?= date('M d, Y h:i A', strtotime($sale['created_at'])) ?></td>
                        <td><code><?= htmlspecialchars($sale['or_number']) ?></code></td>
                        <td><?= htmlspecialchars($sale['cashier_name'] ?? 'N/A') ?></td>
                        <td class="text-center"><?= (int) $sale['item_count'] ?></td>
                        <td class="text-end"><?= formatMoney((float) $sale['discount']) ?></td>
                        <td class="text-end fw-semibold"><?= formatMoney((float) $sale['total']) ?></td>
                        <td class="text-center">
                            <a href="<?= $base ?>/receipt/print.php?sale_id=<?= (int) $sale['id'] ?>"
                               target="_blank" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-receipt"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function exportTable(tableId, filename) {
    const table = document.getElementById(tableId);
    let csv = [];
    table.querySelectorAll('tr').forEach(row => {
        const cols = [];
        row.querySelectorAll('th, td').forEach((cell, i, cells) => {
            if (i === cells.length - 1 && cell.querySelector('a, button')) return;
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

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
