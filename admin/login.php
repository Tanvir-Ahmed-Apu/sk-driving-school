<?php

require_once __DIR__ . '/../includes/admin-auth.php';

if (isset($_SESSION['admin_user_id'])) {
    header('Location: index.php');
    exit;
}

$error = null;
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    if (!validateAdminCsrf()) {
        http_response_code(400);
        $error = 'Your session expired. Refresh the page and try again.';
    } elseif ($pdo === null) {
        http_response_code(503);
        $error = 'Admin sign-in is unavailable because the database is not connected.';
    } else {
        $email = isset($_POST['email']) && is_string($_POST['email']) ? trim($_POST['email']) : '';
        $password = isset($_POST['password']) && is_string($_POST['password']) ? $_POST['password'] : '';
        $remoteAddress = isset($_SERVER['REMOTE_ADDR']) && is_string($_SERVER['REMOTE_ADDR'])
            ? $_SERVER['REMOTE_ADDR']
            : 'unknown';
        $remoteKey = hash('sha256', $remoteAddress);
        $attempts = $pdo->prepare(
            'SELECT COUNT(*) FROM admin_login_failures WHERE remote_key = ? AND created_at >= DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 15 MINUTE)'
        );
        $attempts->execute([$remoteKey]);
        if ((int) $attempts->fetchColumn() >= 10) {
            http_response_code(429);
            $error = 'Too many sign-in attempts. Wait 15 minutes and try again.';
        } else {
            $statement = $pdo->prepare('SELECT id, email, password_hash FROM admin_users WHERE email = ? LIMIT 1');
            $statement->execute([$email]);
            $admin = $statement->fetch();

            if (is_array($admin) && password_verify($password, $admin['password_hash'])) {
                $pdo->prepare('DELETE FROM admin_login_failures WHERE remote_key = ?')->execute([$remoteKey]);
                session_regenerate_id(true);
                $_SESSION['admin_user_id'] = (int) $admin['id'];
                $_SESSION['admin_email'] = $admin['email'];
                $_SESSION['admin_csrf'] = bin2hex(random_bytes(32));
                $pdo->prepare('UPDATE admin_users SET last_login_at = CURRENT_TIMESTAMP WHERE id = ?')
                    ->execute([(int) $admin['id']]);
                header('Location: index.php');
                exit;
            }

            $pdo->prepare('INSERT INTO admin_login_failures (remote_key) VALUES (?)')->execute([$remoteKey]);
            $error = 'The email or password is incorrect.';
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin sign in · SK Driving School</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body class="admin-login-page">
    <main class="admin-login-card">
        <a class="admin-brand" href="../index.php"><span>SK</span> Driving School</a>
        <p class="admin-eyebrow mt-4 mb-2">Administration</p>
        <h1 class="h3 fw-bold mb-2">Sign in</h1>
        <p class="text-secondary mb-4">Manage customer orders and payment status.</p>
        <?php if ($error !== null): ?>
            <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>
        <form method="post" action="login.php">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(adminCsrfToken(), ENT_QUOTES, 'UTF-8') ?>">
            <div class="mb-3">
                <label class="form-label" for="admin-email">Email</label>
                <input class="form-control form-control-lg" type="email" name="email" id="admin-email" autocomplete="username" required>
            </div>
            <div class="mb-4">
                <label class="form-label" for="admin-password">Password</label>
                <input class="form-control form-control-lg" type="password" name="password" id="admin-password" autocomplete="current-password" required>
            </div>
            <button class="btn btn-primary btn-lg w-100" type="submit">Sign in</button>
        </form>
    </main>
</body>
</html>
