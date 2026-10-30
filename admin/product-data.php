<?php

require_once __DIR__ . '/../includes/admin-auth.php';
requireAdminLogin();

if ($pdo === null) {
    http_response_code(503);
    exit('Product data is unavailable because the database is not connected.');
}

$tableStatement = $pdo->prepare(
    'SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?'
);
$tableStatement->execute(['products']);
if ((int) $tableStatement->fetchColumn() === 0) {
    http_response_code(503);
    $adminPageTitle = 'Product data';
    require __DIR__ . '/_header.php';
    ?>
    <div class="alert alert-warning" role="alert">
        <h2 class="h5 alert-heading">Product catalog update required</h2>
        <p class="mb-0">Apply <code>database/migrations/20261024_product_catalog.sql</code> to the configured MySQL database, then reload this page.</p>
    </div>
    <?php
    require __DIR__ . '/_footer.php';
    exit;
}

$regionalServiceAreas = [
    'Glendale', 'Hempstead', 'Hollis', 'Laurelton', 'Long Island',
    'Manhattan', 'Middle Village', 'Queens Village', 'Rego Park', 'Ronkonkoma',
];
$allowedCategories = ['Basic', 'Standard', 'Individual'];
$notice = null;
$error = null;

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $action = $_POST['action'] ?? '';
    if (!validateAdminCsrf()) {
        http_response_code(400);
        $error = 'Your session expired. Refresh the page and try again.';
    } elseif (!is_string($action) || !in_array($action, ['create', 'save'], true)) {
        http_response_code(400);
        $error = 'That product action is not supported.';
    } else {
        $idValue = $_POST['product_id'] ?? '';
        $name = isset($_POST['name']) && is_string($_POST['name']) ? trim($_POST['name']) : '';
        $category = $_POST['category'] ?? '';
        $region = $_POST['region'] ?? '';
        $price = $_POST['price'] ?? '';
        $subtitle = isset($_POST['subtitle']) && is_string($_POST['subtitle']) ? trim($_POST['subtitle']) : '';
        $featureText = isset($_POST['features']) && is_string($_POST['features']) ? trim($_POST['features']) : '';
        $sortValue = $_POST['sort_order'] ?? '0';
        $isActive = ($_POST['is_active'] ?? '') === '1' ? 1 : 0;
        $features = array_values(array_filter(
            array_map('trim', preg_split('/\R/', $featureText) ?: []),
            static fn (string $feature): bool => $feature !== ''
        ));
        $priceIsValid = is_string($price) && preg_match('/\A\d{1,7}(?:\.\d{1,2})?\z/', $price) === 1;
        $categoryIsValid = is_string($category) && in_array($category, $allowedCategories, true);
        $regionIsValid = is_string($region) && ($region === '' || in_array($region, $regionalServiceAreas, true));
        $sortIsValid = is_string($sortValue) && preg_match('/\A-?\d{1,6}\z/', $sortValue) === 1;

        if (
            $name === '' || strlen($name) > 180
            || !$priceIsValid || (float) $price > 9999999.99
            || !$categoryIsValid || !$regionIsValid || !$sortIsValid
            || strlen($subtitle) > 255
            || $features === [] || count($features) > 20
            || array_filter($features, static fn (string $feature): bool => strlen($feature) > 255) !== []
            || ($action === 'save' && (!is_string($idValue) || !ctype_digit($idValue) || (int) $idValue < 1))
        ) {
            http_response_code(422);
            $error = 'Check the product name, category, area, price, sort order, and at least one feature (up to 20 features, 255 characters each).';
        } else {
            $priceCents = (int) round((float) $price * 100);
            $featuresJson = json_encode($features, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
            if ($action === 'create') {
                $insert = $pdo->prepare(
                    'INSERT INTO products (category, region, name, price_cents, subtitle, features, is_active, sort_order)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
                );
                $insert->execute([
                    $category, $region === '' ? null : $region, $name, $priceCents,
                    $subtitle === '' ? null : $subtitle, $featuresJson, $isActive, (int) $sortValue,
                ]);
                $notice = 'Product added to the catalog.';
            } else {
                $update = $pdo->prepare(
                    'UPDATE products
                     SET category = ?, region = ?, name = ?, price_cents = ?, subtitle = ?,
                         features = ?, is_active = ?, sort_order = ?
                     WHERE id = ?'
                );
                $update->execute([
                    $category, $region === '' ? null : $region, $name, $priceCents,
                    $subtitle === '' ? null : $subtitle, $featuresJson, $isActive,
                    (int) $sortValue, (int) $idValue,
                ]);
                $notice = 'Product changes saved.';
            }
        }
    }
}

$filterValues = ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' ? $_POST : $_GET;
$catalogFilter = isset($filterValues['type']) && is_string($filterValues['type']) ? $filterValues['type'] : 'all';
$allowedCatalogFilters = ['all', 'Basic', 'Standard', 'Individual', 'Regional'];
if (!in_array($catalogFilter, $allowedCatalogFilters, true)) {
    $catalogFilter = 'all';
}
$areaFilter = isset($filterValues['area']) && is_string($filterValues['area']) ? $filterValues['area'] : '';
if ($areaFilter !== '' && $areaFilter !== 'global' && !in_array($areaFilter, $regionalServiceAreas, true)) {
    $areaFilter = '';
}
$search = isset($filterValues['q']) && is_string($filterValues['q']) ? trim($filterValues['q']) : '';
$conditions = [];
$parameters = [];
if ($catalogFilter === 'Regional') {
    $conditions[] = 'region IS NOT NULL';
} elseif ($catalogFilter !== 'all') {
    $conditions[] = 'category = ?';
    $parameters[] = $catalogFilter;
}
if ($areaFilter === 'global') {
    $conditions[] = 'region IS NULL';
} elseif ($areaFilter !== '') {
    $conditions[] = 'region = ?';
    $parameters[] = $areaFilter;
}
if ($search !== '') {
    $conditions[] = '(name LIKE ? OR subtitle LIKE ?)';
    $searchPattern = '%' . $search . '%';
    array_push($parameters, $searchPattern, $searchPattern);
}
$sql = 'SELECT id, category, region, name, price_cents, subtitle, features, is_active, sort_order FROM products';
if ($conditions !== []) {
    $sql .= ' WHERE ' . implode(' AND ', $conditions);
}
$sql .= ' ORDER BY CASE WHEN region IS NULL THEN 0 ELSE 1 END, category, region, sort_order, id';
$productStatement = $pdo->prepare($sql);
$productStatement->execute($parameters);
$products = $productStatement->fetchAll();

$adminPageTitle = 'Product data';
require __DIR__ . '/_header.php';
?>
<?php if ($notice !== null): ?><div class="alert alert-success" role="status"><?= adminEscape($notice) ?></div><?php endif; ?>
<?php if ($error !== null): ?><div class="alert alert-danger" role="alert"><?= adminEscape($error) ?></div><?php endif; ?>
<section class="admin-panel">
    <div class="admin-panel-heading">
        <div>
            <h2 class="h5 fw-bold mb-1">Product catalog</h2>
            <p class="text-secondary small mb-0">Edit package names, details, prices, availability, and each service area's regional pricing. Saved changes update the public package page and checkout.</p>
        </div>
    </div>
    <form method="get" action="product-data.php" class="admin-product-filter">
        <div>
            <label class="form-label" for="product-type-filter">Package group</label>
            <select class="form-select" id="product-type-filter" name="type">
                <?php foreach ($allowedCatalogFilters as $filter): ?>
                    <option value="<?= adminEscape($filter) ?>" <?= $catalogFilter === $filter ? 'selected' : '' ?>><?= adminEscape($filter === 'all' ? 'All products' : $filter) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="form-label" for="product-area-filter">Service area</label>
            <select class="form-select" id="product-area-filter" name="area">
                <option value="">All areas</option>
                <option value="global" <?= $areaFilter === 'global' ? 'selected' : '' ?>>All locations / base packages</option>
                <?php foreach ($regionalServiceAreas as $area): ?>
                    <option value="<?= adminEscape($area) ?>" <?= $areaFilter === $area ? 'selected' : '' ?>><?= adminEscape($area) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="admin-product-filter-search">
            <label class="form-label" for="product-search">Search product</label>
            <input class="form-control" id="product-search" name="q" value="<?= adminEscape($search) ?>" placeholder="Name or subtitle">
        </div>
        <button class="btn btn-primary align-self-end" type="submit">Filter</button>
        <a class="btn btn-outline-secondary align-self-end" href="product-data">Reset</a>
    </form>
    <p class="text-secondary small mb-3">Showing <?= count($products) ?> product<?= count($products) === 1 ? '' : 's' ?>.</p>
    <?php if ($products === []): ?>
        <div class="alert alert-info">No catalog products found. Add the initial catalog migration or create a product below.</div>
    <?php endif; ?>
    <div class="admin-product-list">
        <?php foreach ($products as $product): ?>
            <?php
            $features = json_decode($product['features'], true);
            $featureText = is_array($features) ? implode("\n", array_filter($features, 'is_string')) : '';
            ?>
            <details class="admin-product-editor">
                <summary>
                    <span>
                        <strong><?= adminEscape($product['name']) ?></strong>
                        <small><?= adminEscape($product['region'] ? 'Regional · ' . $product['region'] . ' · ' . $product['category'] : $product['category'] . ' · All locations') ?></small>
                    </span>
                    <span class="admin-product-summary-price">$<?= number_format(((int) $product['price_cents']) / 100, 2) ?></span>
                    <span class="admin-status admin-status-<?= $product['is_active'] ? 'confirmed' : 'cancelled' ?>"><?= $product['is_active'] ? 'Active' : 'Hidden' ?></span>
                </summary>
                <form method="post" action="product-data.php" class="admin-product-form">
                    <input type="hidden" name="csrf_token" value="<?= adminEscape(adminCsrfToken()) ?>">
                    <input type="hidden" name="type" value="<?= adminEscape($catalogFilter) ?>">
                    <input type="hidden" name="area" value="<?= adminEscape($areaFilter) ?>">
                    <input type="hidden" name="q" value="<?= adminEscape($search) ?>">
                    <input type="hidden" name="action" value="save">
                    <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                    <div class="admin-product-fields">
                        <div>
                            <label class="form-label" for="name-<?= (int) $product['id'] ?>">Product name</label>
                            <input class="form-control" id="name-<?= (int) $product['id'] ?>" name="name" maxlength="180" value="<?= adminEscape($product['name']) ?>" required>
                        </div>
                        <div>
                            <label class="form-label" for="category-<?= (int) $product['id'] ?>">Category</label>
                            <select class="form-select" id="category-<?= (int) $product['id'] ?>" name="category">
                                <?php foreach ($allowedCategories as $category): ?>
                                    <option value="<?= adminEscape($category) ?>" <?= $product['category'] === $category ? 'selected' : '' ?>><?= adminEscape($category) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="form-label" for="region-<?= (int) $product['id'] ?>">Service area</label>
                            <select class="form-select" id="region-<?= (int) $product['id'] ?>" name="region">
                                <option value="">All locations / Basic &amp; Standard</option>
                                <?php foreach ($regionalServiceAreas as $area): ?>
                                    <option value="<?= adminEscape($area) ?>" <?= $product['region'] === $area ? 'selected' : '' ?>><?= adminEscape($area) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="form-label" for="price-<?= (int) $product['id'] ?>">Price (USD)</label>
                            <input class="form-control" id="price-<?= (int) $product['id'] ?>" name="price" type="number" min="0" max="9999999.99" step="0.01" value="<?= number_format(((int) $product['price_cents']) / 100, 2, '.', '') ?>" required>
                        </div>
                        <div>
                            <label class="form-label" for="subtitle-<?= (int) $product['id'] ?>">Subtitle (optional)</label>
                            <input class="form-control" id="subtitle-<?= (int) $product['id'] ?>" name="subtitle" maxlength="255" value="<?= adminEscape($product['subtitle'] ?? '') ?>">
                        </div>
                        <div>
                            <label class="form-label" for="sort-<?= (int) $product['id'] ?>">Display order</label>
                            <input class="form-control" id="sort-<?= (int) $product['id'] ?>" name="sort_order" type="number" min="-999999" max="999999" value="<?= (int) $product['sort_order'] ?>">
                        </div>
                        <div class="admin-product-feature-field">
                            <label class="form-label" for="features-<?= (int) $product['id'] ?>">Features (one per line)</label>
                            <textarea class="form-control" id="features-<?= (int) $product['id'] ?>" name="features" rows="4" required><?= adminEscape($featureText) ?></textarea>
                        </div>
                    </div>
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mt-3">
                        <label class="form-check-label">
                            <input type="hidden" name="is_active" value="0">
                            <input class="form-check-input me-1" type="checkbox" name="is_active" value="1" <?= $product['is_active'] ? 'checked' : '' ?>>
                            Available on public site
                        </label>
                        <button class="btn btn-primary" type="submit">Save product</button>
                    </div>
                </form>
            </details>
        <?php endforeach; ?>
    </div>
</section>

<section class="admin-panel">
    <div class="admin-panel-heading">
        <div>
            <h2 class="h5 fw-bold mb-1">Add product</h2>
            <p class="text-secondary small mb-0">Choose a service area to create a regional version with its own price. Leave it at all locations for a Basic, Standard, or Individual product.</p>
        </div>
    </div>
    <form method="post" action="product-data.php" class="admin-product-form">
        <input type="hidden" name="csrf_token" value="<?= adminEscape(adminCsrfToken()) ?>">
        <input type="hidden" name="type" value="<?= adminEscape($catalogFilter) ?>">
        <input type="hidden" name="area" value="<?= adminEscape($areaFilter) ?>">
        <input type="hidden" name="q" value="<?= adminEscape($search) ?>">
        <input type="hidden" name="action" value="create">
        <div class="admin-product-fields">
            <div>
                <label class="form-label" for="new-name">Product name</label>
                <input class="form-control" id="new-name" name="name" maxlength="180" required>
            </div>
            <div>
                <label class="form-label" for="new-category">Category</label>
                <select class="form-select" id="new-category" name="category" required>
                    <?php foreach ($allowedCategories as $category): ?><option value="<?= adminEscape($category) ?>"><?= adminEscape($category) ?></option><?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="form-label" for="new-region">Service area</label>
                <select class="form-select" id="new-region" name="region">
                    <option value="">All locations / Basic &amp; Standard</option>
                    <?php foreach ($regionalServiceAreas as $area): ?><option value="<?= adminEscape($area) ?>"><?= adminEscape($area) ?></option><?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="form-label" for="new-price">Price (USD)</label>
                <input class="form-control" id="new-price" name="price" type="number" min="0" max="9999999.99" step="0.01" required>
            </div>
            <div>
                <label class="form-label" for="new-subtitle">Subtitle (optional)</label>
                <input class="form-control" id="new-subtitle" name="subtitle" maxlength="255">
            </div>
            <div>
                <label class="form-label" for="new-sort">Display order</label>
                <input class="form-control" id="new-sort" name="sort_order" type="number" value="100">
            </div>
            <div class="admin-product-feature-field">
                <label class="form-label" for="new-features">Features (one per line)</label>
                <textarea class="form-control" id="new-features" name="features" rows="4" required></textarea>
            </div>
        </div>
        <div class="d-flex justify-content-between align-items-center gap-3 mt-3">
            <label class="form-check-label"><input type="hidden" name="is_active" value="0"><input class="form-check-input me-1" type="checkbox" name="is_active" value="1" checked> Available on public site</label>
            <button class="btn btn-primary" type="submit">Add product</button>
        </div>
    </form>
</section>
<?php require __DIR__ . '/_footer.php'; ?>
