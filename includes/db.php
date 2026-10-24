<?php

if (is_file(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}
if (class_exists(Dotenv\Dotenv::class) && is_file(__DIR__ . '/../.env')) {
    Dotenv\Dotenv::createImmutable(dirname(__DIR__))->safeLoad();
}

function appEnv(string $key, string $default = ''): string
{
    $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
    return is_string($value) && $value !== '' ? $value : $default;
}

$pdo = null;
$databaseHost = appEnv('DB_HOST', '127.0.0.1');
$databasePort = appEnv('DB_PORT', '3306');
$databaseName = appEnv('DB_NAME', 'sk_driving_school');
$databaseUser = appEnv('DB_USER', 'root');
$databasePassword = appEnv('DB_PASSWORD');

try {
    $pdo = new PDO(
        "mysql:host={$databaseHost};port={$databasePort};dbname={$databaseName};charset=utf8mb4",
        $databaseUser,
        $databasePassword,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    error_log('Database connection failed: ' . $e->getMessage());
}

function saveContactSubmission(array $submission): bool
{
    $storageDir = __DIR__ . '/../database';
    if (!is_dir($storageDir) && !mkdir($storageDir, 0775, true) && !is_dir($storageDir)) {
        return false;
    }

    $entry = [
        'created_at' => gmdate('c'),
        'name' => $submission['name'] ?? '',
        'phone' => $submission['phone'] ?? '',
        'email' => $submission['email'] ?? '',
        'message' => $submission['message'] ?? '',
    ];
    $line = json_encode($entry, JSON_UNESCAPED_SLASHES);

    return $line !== false
        && file_put_contents($storageDir . '/contact_messages.jsonl', $line . PHP_EOL, FILE_APPEND | LOCK_EX) !== false;
}
