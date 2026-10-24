<?php

require_once __DIR__ . '/db.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    $isHttps = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    session_set_cookie_params([
        'httponly' => true,
        'secure' => $isHttps,
        'samesite' => 'Strict',
        'path' => '/',
    ]);
    session_start();
}

function adminCsrfToken(): string
{
    if (!isset($_SESSION['admin_csrf']) || !is_string($_SESSION['admin_csrf'])) {
        $_SESSION['admin_csrf'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['admin_csrf'];
}

function requireAdminLogin(): void
{
    header('Cache-Control: no-store, private');
    if (!isset($_SESSION['admin_user_id']) || !is_int($_SESSION['admin_user_id'])) {
        header('Location: login.php');
        exit;
    }
}

function validateAdminCsrf(): bool
{
    $submitted = $_POST['csrf_token'] ?? '';
    return is_string($submitted)
        && isset($_SESSION['admin_csrf'])
        && is_string($_SESSION['admin_csrf'])
        && hash_equals($_SESSION['admin_csrf'], $submitted);
}

function adminEscape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
