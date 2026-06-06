<?php
$pageTitle = 'Users';
$adminOnly = true;
$pageScripts = ['users.js'];

require_once __DIR__ . '/../includes/header.php';

$users = $conn->query(
    'SELECT id, name, username, role, is_active, created_at FROM users ORDER BY name ASC'
)->fetch_all(MYSQLI_ASSOC);
$currentUserId = (int) $_SESSION['user_id'];
?>

<div id="usersApp" data-base-url="<?= htmlspecialchars($base) ?>">
    <div class="card stat-card">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h6 class="mb-0">User Accounts</h6>
            <button type="button" class="btn btn-primary btn-sm" id="btnAddUser">
                <i class="bi bi-person-plus"></i> Add User
            </button>
        </div>
        <div class="card-body">
            <table class="table table-hover datatable" id="usersTable">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= htmlspecialchars($user['name']) ?></td>
                            <td><code><?= htmlspecialchars($user['username']) ?></code></td>
                            <td><span class="badge bg-<?= $user['role'] === 'admin' ? 'primary' : 'secondary' ?>"><?= htmlspecialchars(ucfirst($user['role'])) ?></span></td>
                            <td>
                                <?php if ((int) $user['is_active']): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td><?= date('M d, Y', strtotime($user['created_at'])) ?></td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-outline-primary btn-edit-user"
                                    data-user='<?= htmlspecialchars(json_encode($user), ENT_QUOTES) ?>'>
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <?php if ((int) $user['id'] !== $currentUserId): ?>
                                    <button class="btn btn-sm btn-outline-<?= (int) $user['is_active'] ? 'warning' : 'success' ?> btn-toggle-user"
                                        data-id="<?= (int) $user['id'] ?>"
                                        data-active="<?= (int) $user['is_active'] ?>"
                                        data-name="<?= htmlspecialchars($user['name']) ?>">
                                        <i class="bi bi-<?= (int) $user['is_active'] ? 'pause' : 'play' ?>"></i>
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="userModal" tabindex="-1">
    <div class="modal-dialog">
        <form class="modal-content" id="userForm">
            <div class="modal-header">
                <h5 class="modal-title" id="userModalTitle">Add User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="userId">
                <div class="mb-3">
                    <label class="form-label">Full Name</label>
                    <input type="text" class="form-control" name="name" id="userName" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <input type="text" class="form-control" name="username" id="userUsername" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password <small class="text-muted" id="passwordHint">(required)</small></label>
                    <input type="password" class="form-control" name="password" id="userPassword">
                </div>
                <div class="mb-3">
                    <label class="form-label">Role</label>
                    <select class="form-select" name="role" id="userRole">
                        <option value="cashier">Cashier</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="userActive" checked>
                    <label class="form-check-label" for="userActive">Active</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Save User</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
