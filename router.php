<?php
$documentRoot = realpath(__DIR__);
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$requestPath = parse_url($requestUri, PHP_URL_PATH);

if ($documentRoot === false || !is_string($requestPath)) {
    http_response_code(400);
    exit;
}

$decodedPath = rawurldecode($requestPath);
$requestedFile = realpath($documentRoot . DIRECTORY_SEPARATOR . ltrim($decodedPath, '/'));
$isWithinDocumentRoot = static fn(string $path): bool =>
    $path === $documentRoot || str_starts_with($path, $documentRoot . DIRECTORY_SEPARATOR);

if ($requestedFile !== false && $isWithinDocumentRoot($requestedFile) && is_file($requestedFile)) {
    if (strtolower(pathinfo($requestedFile, PATHINFO_EXTENSION)) !== 'php') {
        return false;
    }

    if (preg_match('/\.php$/i', $requestPath) && in_array($_SERVER['REQUEST_METHOD'] ?? '', ['GET', 'HEAD'], true)) {
        $cleanPath = substr($requestPath, 0, -4) ?: '/';
        $query = parse_url($requestUri, PHP_URL_QUERY);
        header('Location: ' . $cleanPath . ($query !== null ? '?' . $query : ''), true, 301);
        exit;
    }

    return false;
}

$scriptFile = realpath($documentRoot . DIRECTORY_SEPARATOR . ltrim($decodedPath . '.php', '/'));
if ($scriptFile === false || !$isWithinDocumentRoot($scriptFile) || !is_file($scriptFile)) {
    return false;
}

$scriptPath = $requestPath . '.php';
$_SERVER['SCRIPT_FILENAME'] = $scriptFile;
$_SERVER['SCRIPT_NAME'] = $scriptPath;
$_SERVER['PHP_SELF'] = $scriptPath;

require $scriptFile;
