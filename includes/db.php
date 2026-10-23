<?php
// Database connection with fallback support for local development and hosted environments.
$pdo = null;
$dsn = "mysql:host=127.0.0.1;dbname=sk_driving_school;charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, 'root', '', $options);
} catch (\PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    $pdo = null;
}

function saveContactSubmission(array $submission): bool
{
    $storageDir = __DIR__ . '/../database';
    if (!is_dir($storageDir) && !mkdir($storageDir, 0775, true) && !is_dir($storageDir)) {
        return false;
    }

    $logFile = $storageDir . '/contact_messages.jsonl';
    $entry = [
        'created_at' => gmdate('c'),
        'name' => $submission['name'] ?? '',
        'phone' => $submission['phone'] ?? '',
        'email' => $submission['email'] ?? '',
        'message' => $submission['message'] ?? '',
    ];

    $line = json_encode($entry, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    if ($line === false) {
        return false;
    }

    return (bool) file_put_contents($logFile, $line . PHP_EOL, FILE_APPEND | LOCK_EX);
}