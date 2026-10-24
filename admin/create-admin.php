<?php

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/../includes/db.php';

$email = trim(appEnv('ADMIN_EMAIL'));
$password = appEnv('ADMIN_PASSWORD');

if ($pdo === null) {
    fwrite(STDERR, "Database is unavailable. Configure DB_* values and apply database/schema.sql first.\n");
    exit(1);
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 12) {
    fwrite(STDERR, "Set ADMIN_EMAIL and ADMIN_PASSWORD in the environment. The password must be at least 12 characters.\n");
    exit(1);
}

$statement = $pdo->prepare(
    'INSERT INTO admin_users (email, password_hash) VALUES (:email, :password_hash)'
);
try {
    $statement->execute([
        'email' => $email,
        'password_hash' => password_hash($password, PASSWORD_DEFAULT),
    ]);
} catch (PDOException $e) {
    if ($e->getCode() === '23000') {
        fwrite(STDERR, "An admin account with that email already exists.\n");
        exit(1);
    }
    throw $e;
}

fwrite(STDOUT, "Admin account created for {$email}.\n");
