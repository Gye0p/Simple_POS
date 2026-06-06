<?php
$pageTitle = 'Settings';
$adminOnly = true;
$pageScripts = ['settings.js'];

require_once __DIR__ . '/../includes/header.php';

$settings = [
    'store_name'          => getSetting($conn, 'store_name', 'My Convenience Store'),
    'store_address'       => getSetting($conn, 'store_address', ''),
    'store_tin'           => getSetting($conn, 'store_tin', ''),
    'receipt_footer'      => getSetting($conn, 'receipt_footer', 'Thank you for shopping with us!'),
    'low_stock_threshold' => getSetting($conn, 'low_stock_threshold', '5'),
];
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card stat-card">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-gear"></i> Store Settings</h6>
            </div>
            <div class="card-body">
                <form id="settingsForm" data-base-url="<?= htmlspecialchars($base) ?>">
                    <div class="mb-3">
                        <label class="form-label">Store Name</label>
                        <input type="text" class="form-control" name="store_name" value="<?= htmlspecialchars($settings['store_name']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Store Address</label>
                        <textarea class="form-control" name="store_address" rows="2"><?= htmlspecialchars($settings['store_address']) ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">TIN</label>
                        <input type="text" class="form-control" name="store_tin" value="<?= htmlspecialchars($settings['store_tin']) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Receipt Footer Message</label>
                        <input type="text" class="form-control" name="receipt_footer" value="<?= htmlspecialchars($settings['receipt_footer']) ?>">
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Low Stock Threshold</label>
                        <input type="number" class="form-control" name="low_stock_threshold" value="<?= htmlspecialchars($settings['low_stock_threshold']) ?>" min="1" style="max-width: 150px;">
                        <small class="text-muted">Products with stock below this number trigger low-stock alerts.</small>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg"></i> Save Settings
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
