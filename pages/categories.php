<?php
$pageTitle = 'Categories';
$adminOnly = true;
$pageScripts = ['categories.js'];

require_once __DIR__ . '/../includes/header.php';
?>

<div id="categoriesApp" data-base-url="<?= htmlspecialchars($base) ?>">
    <div class="card stat-card">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h6 class="mb-0">Product Categories</h6>
            <button type="button" class="btn btn-primary btn-sm" id="btnAddCategory">
                <i class="bi bi-plus-lg"></i> Add Category
            </button>
        </div>
        <div class="card-body">
            <table class="table table-hover" id="categoriesTable">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th class="text-center">Products</th>
                        <th class="text-center" style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="categoryModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <form class="modal-content" id="categoryForm">
            <div class="modal-header">
                <h5 class="modal-title" id="categoryModalTitle">Add Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="categoryId">
                <label class="form-label">Category Name</label>
                <input type="text" class="form-control" name="name" id="categoryName" required>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
