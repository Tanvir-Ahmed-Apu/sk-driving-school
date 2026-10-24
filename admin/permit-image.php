<?php

require_once __DIR__ . '/../includes/admin-auth.php';
require_once __DIR__ . '/../includes/permit-documents.php';
requireAdminLogin();

if ($pdo === null) {
    http_response_code(503);
    exit('Permit image is unavailable.');
}

$idValue = $_GET['id'] ?? '';
if (!is_string($idValue) || !ctype_digit($idValue) || (int) $idValue < 1) {
    http_response_code(404);
    exit('Permit image not found.');
}

$statement = $pdo->prepare('SELECT storage_key, mime_type FROM order_documents WHERE id = ?');
$statement->execute([(int) $idValue]);
$document = $statement->fetch();
if (
    !is_array($document)
    || !is_string($document['storage_key'])
    || preg_match('/\A[a-f0-9]{64}\z/', $document['storage_key']) !== 1
    || !in_array($document['mime_type'], ['image/jpeg', 'image/png', 'image/webp'], true)
) {
    http_response_code(404);
    exit('Permit image not found.');
}

$path = permitUploadDirectory() . DIRECTORY_SEPARATOR . $document['storage_key'];
if (!is_file($path) || !is_readable($path)) {
    http_response_code(404);
    exit('Permit image is unavailable.');
}

header('Content-Type: ' . $document['mime_type']);
header('Content-Disposition: inline');
header('Cache-Control: no-store, private');
header('X-Content-Type-Options: nosniff');
header("Content-Security-Policy: default-src 'none'; sandbox");
readfile($path);
