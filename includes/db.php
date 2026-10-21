<?php
// Database connection with fallback
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
}