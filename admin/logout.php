<?php

require_once __DIR__ . '/../includes/admin-auth.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST' || !validateAdminCsrf()) {
    http_response_code(400);
    exit('Invalid logout request.');
}

$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $cookie = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $cookie['path'], $cookie['domain'], $cookie['secure'], $cookie['httponly']);
}
session_destroy();
header('Location: login.php');
exit;