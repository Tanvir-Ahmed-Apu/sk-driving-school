<?php

require_once __DIR__ . '/../includes/admin-auth.php';
requireAdminLogin();

$error = null;
$notice = null;
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    if (!validateAdminCsrf()) {
        http_response_code(400);
        $error = 'Your session expired. Refresh the page and try again.';
    } elseif ($pdo === null) {
        http_response_code(503);
        $error = 'Password updates are unavailable because the database is not connected.';
    } else {
        $currentPassword = isset($_POST['current_password']) && is_string($_POST['current_password'])
            ? $_POST['current_password']
            : '';
        $newPassword = isset($_POST['new_password']) && is_string($_POST['new_password'])
            ? $_POST['new_password']
            : '';
        $confirmPassword = isset($_POST['confirm_password']) && is_string($_POST['confirm_password'])
            ? $_POST['confirm_password']
            : '';

        if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
            http_response_code(422);
            $error = 'Enter your current password and both new-password fields.';
        } elseif (strlen($newPassword) < 12) {
            http_response_code(422);
            $error = 'Your new password must be at least 12 characters long.';
        } elseif (strlen($newPassword) > 4096) {
            http_response_code(422);
            $error = 'Your new password is too long.';
        } elseif (!hash_equals($newPassword, $confirmPassword)) {
            http_response_code(422);
            $error = 'The new password and confirmation do not match.';
        } elseif (hash_equals($currentPassword, $newPassword)) {
            http_response_code(422);
            $error = 'Choose a new password that is different from your current password.';
        } else {
            try {
                $statement = $pdo->prepare('SELECT password_hash FROM admin_users WHERE id = ? LIMIT 1');
                $statement->execute([(int) $_SESSION['admin_user_id']]);
                $admin = $statement->fetch();

                if (!is_array($admin) || !password_verify($currentPassword, $admin['password_hash'])) {
                    http_response_code(422);
                    $error = 'Your current password is incorrect.';
                } else {
                    $update = $pdo->prepare('UPDATE admin_users SET password_hash = ? WHERE id = ?');
                    $update->execute([
                        password_hash($newPassword, PASSWORD_DEFAULT),
                        (int) $_SESSION['admin_user_id'],
                    ]);
                    if ($update->rowCount() !== 1) {
                        throw new RuntimeException('Password update did not change the admin record.');
                    }

                    session_regenerate_id(true);
                    $_SESSION['admin_csrf'] = bin2hex(random_bytes(32));
                    $notice = 'Your password has been updated.';
                }
            } catch (PDOException | RuntimeException $e) {
                error_log('Could not update admin password: ' . $e->getMessage());
                http_response_code(503);
                $error = 'Your password could not be updated right now. Please try again.';
            }
        }
    }
}

$adminPageTitle = 'Change password';
require __DIR__ . '/_header.php';
?>
<section class="admin-panel admin-password-panel">
    <div class="admin-panel-heading">
        <div>
            <h2 class="h5 fw-bold mb-1">Update your password</h2>
            <p class="text-secondary small mb-0">Confirm your current password, then choose a new password of at least 12 characters.</p>
        </div>
        <span class="admin-stat-icon icon-blue"><i class="bi bi-shield-lock"></i></span>
    </div>
    <?php if ($notice !== null): ?><div class="alert alert-success" role="status"><?= adminEscape($notice) ?></div><?php endif; ?>
    <?php if ($error !== null): ?><div class="alert alert-danger" role="alert"><?= adminEscape($error) ?></div><?php endif; ?>
    <form method="post" action="change-password.php" class="admin-password-form">
        <input type="hidden" name="csrf_token" value="<?= adminEscape(adminCsrfToken()) ?>">
        <div class="mb-3">
            <label class="form-label fw-semibold" for="current-password">Current password</label>
            <input class="form-control form-control-lg" type="password" id="current-password" name="current_password" autocomplete="current-password" required>
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold" for="new-password">New password</label>
            <input class="form-control form-control-lg" type="password" id="new-password" name="new_password" autocomplete="new-password" minlength="12" maxlength="4096" required>
            <div class="form-text">Use at least 12 characters. Avoid reusing a password from another account.</div>
        </div>
        <div class="mb-4">
            <label class="form-label fw-semibold" for="confirm-password">Confirm new password</label>
            <input class="form-control form-control-lg" type="password" id="confirm-password" name="confirm_password" autocomplete="new-password" minlength="12" maxlength="4096" required>
        </div>
        <button class="btn btn-primary" type="submit">Update password</button>
    </form>
</section>
<?php require __DIR__ . '/_footer.php'; ?>
