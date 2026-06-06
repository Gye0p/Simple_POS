<?php
session_start();

require_once __DIR__ . '/../config/db.php';

if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: ../pages/dashboard.php');
    } else {
        header('Location: ../pages/cashier.php');
    }
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Please enter both username and password.';
    } else {
        $stmt = $conn->prepare(
            'SELECT id, name, username, password, role, is_active
             FROM users
             WHERE username = ?
             LIMIT 1'
        );
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if (!$user) {
            $error = 'Invalid username or password.';
        } elseif (!(int) $user['is_active']) {
            $error = 'This account has been deactivated. Contact your administrator.';
        } elseif (!password_verify($password, $user['password'])) {
            $error = 'Invalid username or password.';
        } else {
            session_regenerate_id(true);

            $_SESSION['user_id']  = (int) $user['id'];
            $_SESSION['name']     = $user['name'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role']     = $user['role'];

            if ($user['role'] === 'admin') {
                header('Location: ../pages/dashboard.php');
            } else {
                header('Location: ../pages/cashier.php');
            }
            exit;
        }
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — POS System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #1e3a5f 0%, #0d1b2a 100%);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            width: 100%;
            max-width: 420px;
            border: none;
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }
        .login-header {
            background: #0d1b2a;
            color: #fff;
            border-radius: 12px 12px 0 0;
            padding: 2rem;
            text-align: center;
        }
        .login-header .icon {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }
        .btn-login {
            background: #1e3a5f;
            border-color: #1e3a5f;
            padding: 0.6rem;
            font-weight: 600;
        }
        .btn-login:hover {
            background: #2a5080;
            border-color: #2a5080;
        }
    </style>
</head>
<body>
    <div class="card login-card">
        <div class="login-header">
            <div class="icon">🏪</div>
            <h4 class="mb-0">Convenience Store POS</h4>
            <small class="text-white-50">Sign in to continue</small>
        </div>
        <div class="card-body p-4">
            <?php if ($error !== ''): ?>
                <div class="alert alert-danger py-2" role="alert">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" autocomplete="off">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input
                        type="text"
                        class="form-control"
                        id="username"
                        name="username"
                        value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                        required
                        autofocus
                    >
                </div>
                <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <input
                        type="password"
                        class="form-control"
                        id="password"
                        name="password"
                        required
                    >
                </div>
                <button type="submit" class="btn btn-primary btn-login w-100">
                    Sign In
                </button>
            </form>
        </div>
    </div>
</body>
</html>
