<?php

require_once __DIR__ . '/db.php';

$regionalServiceAreas = [
    'Glendale',
    'Hempstead',
    'Hollis',
    'Laurelton',
    'Long Island',
    'Manhattan',
    'Middle Village',
    'Queens Village',
    'Rego Park',
    'Ronkonkoma',
];

$packagesByCategory = [];
$regionalPackagesByArea = [];
$productsById = [];
$productCatalogError = null;

$productCatalogReady = false;
if ($pdo === null) {
    $productCatalogError = 'The package catalog is unavailable because the database is not connected.';
} else {
    $tableStatement = $pdo->prepare(
        'SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?'
    );
    $tableStatement->execute(['products']);
    $productCatalogReady = (int) $tableStatement->fetchColumn() === 1;
    if (!$productCatalogReady) {
        $productCatalogError = 'The product catalog database update is required. Apply database/migrations/20261024_product_catalog.sql.';
    }
}

if ($productCatalogReady) {
    $statement = $pdo->query(
        'SELECT id, category, region, name, price_cents, subtitle, features, sort_order
         FROM products
         WHERE is_active = 1
         ORDER BY sort_order, id'
    );

    foreach ($statement->fetchAll() as $row) {
        $features = json_decode($row['features'], true);
        if (!is_array($features)) {
            error_log('Skipped product with invalid feature data: ' . (int) $row['id']);
            continue;
        }
        $features = array_values(array_filter($features, 'is_string'));
        $product = [
            'id' => (int) $row['id'],
            'category' => $row['category'],
            'region' => $row['region'] ?? '',
            'name' => $row['name'],
            'price' => ((int) $row['price_cents']) / 100,
            'features' => $features,
            'sort_order' => (int) $row['sort_order'],
        ];
        if (is_string($row['subtitle']) && $row['subtitle'] !== '') {
            $product['subtitle'] = $row['subtitle'];
        }

        $productsById[$product['id']] = $product;
        if ($product['region'] === '') {
            $packagesByCategory[$product['category']][] = $product;
        } else {
            $regionalPackagesByArea[$product['region']][$product['category']][] = $product;
        }
    }

    if (session_status() === PHP_SESSION_ACTIVE && ($_SESSION['catalog_product_ids'] ?? false) !== true) {
        $migratedCart = [];
        foreach ($_SESSION['cart'] ?? [] as $itemId => $quantity) {
            if (!is_string($itemId) || !is_int($quantity) || $quantity < 1 || $quantity > 99) {
                continue;
            }
            $parts = explode('|', $itemId);
            if (count($parts) !== 3 || !ctype_digit($parts[2])) {
                continue;
            }
            [$region, $category, $oldIndex] = $parts;
            $oldIndex = (int) $oldIndex;
            $legacyPackages = $region === ''
                ? ($packagesByCategory[$category] ?? [])
                : ($regionalPackagesByArea[$region][$category] ?? []);
            if (!isset($legacyPackages[$oldIndex])) {
                error_log('Skipped a legacy cart item that no longer exists in the editable package catalog.');
                continue;
            }

            $product = $legacyPackages[$oldIndex];
            $newItemId = $region . '|' . $category . '|' . $product['id'];
            $migratedCart[$newItemId] = min(99, ($migratedCart[$newItemId] ?? 0) + $quantity);
        }
        if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
            $_SESSION['cart'] = $migratedCart;
        }
        $_SESSION['catalog_product_ids'] = true;
    }
}
