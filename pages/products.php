<?php
$pageTitle = 'Products';
$adminOnly = true;
$pageScripts = ['products.js'];

require_once __DIR__ . '/../includes/header.php';

$categories = $conn->query('SELECT id, name FROM categories ORDER BY name ASC')->fetch_all(MYSQLI_ASSOC);
?>

<div id="productsApp" data-base-url="<?= htmlspecialchars($base) ?>">
    <div class="card stat-card">
        <div class="card-header bg-white d-flex flex-wrap gap-2 justify-content-between align-items-center">
            <div class="d-flex gap-2 align-items-center">
                <select id="filterCategory" class="form-select form-select-sm" style="width: 200px;">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= (int) $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-secondary btn-sm" id="btnBulkStock">
                    <i class="bi bi-box-arrow-in-down"></i> Bulk Stock
                </button>
                <button type="button" class="btn btn-primary btn-sm" id="btnAddProduct">
                    <i class="bi bi-plus-lg"></i> Add Product
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="productsTable">
                    <thead class="table-light">
                        <tr>
                            <th>Barcode</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th class="text-end">Price</th>
                            <th class="text-end">Cost</th>
                            <th class="text-center">Stock</th>
                            <th class="text-center">Status</th>
                            <th class="text-center" style="width: 100px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="productModal" tabindex="-1">
    <div class="modal-dialog">
        <form class="modal-content" id="productForm">
            <div class="modal-header">
                <h5 class="modal-title" id="productModalTitle">Add Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="productId">
                <div class="mb-3">
                    <label class="form-label">Barcode</label>
                    <input type="text" class="form-control" name="barcode" id="productBarcode" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Name</label>
                    <input type="text" class="form-control" name="name" id="productName" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Category</label>
                    <select class="form-select" name="category_id" id="productCategory">
                        <option value="">— None —</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= (int) $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="row">
                    <div class="col-6 mb-3">
                        <label class="form-label">Price (₱)</label>
                        <input type="number" class="form-control" name="price" id="productPrice" min="0" step="0.01" required>
                    </div>
                    <div class="col-6 mb-3">
                        <label class="form-label">Cost Price (₱)</label>
                        <input type="number" class="form-control" name="cost_price" id="productCost" min="0" step="0.01">
                    </div>
                </div>
                <div class="row">
                    <div class="col-6 mb-3">
                        <label class="form-label">Stock Qty</label>
                        <input type="number" class="form-control" name="stock" id="productStock" min="0">
                    </div>
                    <div class="col-6 mb-3">
                        <label class="form-label">Unit</label>
                        <input type="text" class="form-control" name="unit" id="productUnit" value="pcs">
                    </div>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="productActive" checked>
                    <label class="form-check-label" for="productActive">Active</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Product</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="stockModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bulk Stock Adjustment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small">Enter positive numbers to add stock, negative to reduce.</p>
                <div class="table-responsive">
                    <table class="table table-sm" id="stockTable">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th class="text-center">Current</th>
                                <th>Adjustment</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="btnSaveStock">Save Adjustments</button>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
