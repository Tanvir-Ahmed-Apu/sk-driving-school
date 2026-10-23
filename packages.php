<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'includes/package-catalog.php';
$cartToken = $_SESSION['cart_token'] ?? '';
if (!is_string($cartToken) || $cartToken === '') {
    $cartToken = bin2hex(random_bytes(32));
    $_SESSION['cart_token'] = $cartToken;
}

$cartNotice = null;
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && ($_POST['action'] ?? '') === 'add_to_cart') {
    $submittedToken = $_POST['cart_token'] ?? '';
    $category = $_POST['category'] ?? '';
    $indexValue = $_POST['package_index'] ?? '';
    $region = $_POST['region'] ?? '';
    $packageIndex = is_string($indexValue) && ctype_digit($indexValue) ? (int) $indexValue : -1;

    if (
        !is_string($submittedToken)
        || !hash_equals($cartToken, $submittedToken)
        || !is_string($category)
        || !isset($packagesByCategory[$category][$packageIndex])
        || !is_string($region)
        || ($region !== '' && !in_array($region, $regionalServiceAreas, true))
    ) {
        http_response_code(400);
        $cartNotice = 'We could not add that package. Please refresh the page and try again.';
    } else {
        $cart = $_SESSION['cart'] ?? [];
        if (!is_array($cart)) {
            $cart = [];
        }

        $itemId = $region . '|' . $category . '|' . $packageIndex;
        $currentQuantity = isset($cart[$itemId]) && is_int($cart[$itemId]) ? $cart[$itemId] : 0;
        if ($currentQuantity >= 99) {
            $cartNotice = 'The maximum quantity for this package is 99.';
        } else {
            $cart[$itemId] = $currentQuantity + 1;
            $_SESSION['cart'] = $cart;
            $_SESSION['cart_notice'] = $packagesByCategory[$category][$packageIndex]['name'] . ' added to your cart.';
            $redirect = 'packages.php';
            if ($region !== '') {
                $redirect .= '?region=' . rawurlencode($region);
            }
            header('Location: ' . $redirect);
            exit;
        }
    }
}

if (isset($_SESSION['cart_notice']) && is_string($_SESSION['cart_notice'])) {
    $cartNotice = $_SESSION['cart_notice'];
    unset($_SESSION['cart_notice']);
}

$selectedRegion = null;
if (isset($_GET['region']) && is_string($_GET['region']) && in_array($_GET['region'], $regionalServiceAreas, true)) {
    $selectedRegion = $_GET['region'];
}

function renderPackageCards(array $packages, string $category, string $region, string $cartToken): void
{
    foreach ($packages as $index => $package): ?>
        <div class="col-lg-4 col-md-6">
            <article class="card border-0 shadow-sm rounded-4 h-100 p-4 d-flex flex-column">
                <span class="badge bg-light text-dark border align-self-start mb-3">
                    <?= htmlspecialchars($package['category'], ENT_QUOTES, 'UTF-8') ?>
                </span>
                <h3 class="h4 fw-bold"><?= htmlspecialchars($package['name'], ENT_QUOTES, 'UTF-8') ?></h3>
                <?php if (isset($package['subtitle'])): ?>
                    <p class="text-muted small text-uppercase"><?= htmlspecialchars($package['subtitle'], ENT_QUOTES, 'UTF-8') ?></p>
                <?php endif; ?>
                <div class="d-flex align-items-baseline mb-4">
                    <span class="h2 fw-bold mb-0">$<?= number_format($package['price']) ?></span>
                </div>
                <ul class="list-unstyled small text-muted my-3 border-top pt-4 flex-grow-1">
                    <?php foreach ($package['features'] as $feature): ?>
                        <li class="mb-2"><i class="bi bi-check2 text-primary me-2" aria-hidden="true"></i><?= htmlspecialchars($feature, ENT_QUOTES, 'UTF-8') ?></li>
                    <?php endforeach; ?>
                </ul>
                <form method="post" action="packages.php" class="mt-auto">
                    <input type="hidden" name="action" value="add_to_cart">
                    <input type="hidden" name="cart_token" value="<?= htmlspecialchars($cartToken, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="category" value="<?= htmlspecialchars($category, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="package_index" value="<?= (int) $index ?>">
                    <input type="hidden" name="region" value="<?= htmlspecialchars($region, ENT_QUOTES, 'UTF-8') ?>">
                    <button type="submit" class="btn btn-primary w-100 rounded-pill py-2 fw-semibold">
                        <i class="bi bi-cart-plus me-2" aria-hidden="true"></i>Add to cart
                    </button>
                </form>
            </article>
        </div>
    <?php endforeach;
}

$categoryPackages = [];
foreach ($packagesByCategory as $category => $packages) {
    foreach ($packages as $package) {
        $categoryPackages[$category][] = $package + ['category' => $category];
    }

    include 'includes/header.php';
}
?>
<header class="site-header">
    <?php
    require_once 'includes/topbar.php';
    require_once 'includes/navbar.php';
    ?>
</header>

<section class="py-5 bg-white border-bottom">
    <div class="container py-4">
        <span class="text-primary fw-bold small text-uppercase tracking-wide">Program Selection</span>
        <div class="d-flex justify-content-between align-items-end mt-2">
            <h1 class="display-5 fw-bold mb-0">Our Packages</h1>
            <p class="text-muted mb-0 d-none d-md-block" style="max-width: 400px;">Choose a regional package or browse our Basic and Standard lesson options.</p>
        </div>
    </div>
</section>

<section class="py-5 bg-light">
    <div class="container">
        <?php if ($cartNotice !== null): ?>
            <div class="alert alert-<?= http_response_code() === 400 ? 'danger' : 'success' ?> alert-dismissible fade show" role="status">
                <?= htmlspecialchars($cartNotice, ENT_QUOTES, 'UTF-8') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <ul class="nav nav-pills mb-5 gap-2" id="package-tabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active rounded-pill px-4" id="regional-tab" data-bs-toggle="pill" data-bs-target="#regional-panel" type="button" role="tab" aria-controls="regional-panel" aria-selected="true">Regional Packages</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-pill px-4" id="basic-tab" data-bs-toggle="pill" data-bs-target="#basic-panel" type="button" role="tab" aria-controls="basic-panel" aria-selected="false">Basic</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-pill px-4" id="standard-tab" data-bs-toggle="pill" data-bs-target="#standard-panel" type="button" role="tab" aria-controls="standard-panel" aria-selected="false">Standard</button>
            </li>
        </ul>

        <div class="tab-content" id="package-tabs-content">
            <div class="tab-pane fade show active" id="regional-panel" role="tabpanel" aria-labelledby="regional-tab" tabindex="0">
                <section class="mb-5" id="regional-packages" aria-labelledby="regional-areas-heading">
                    <div class="mb-4">
                        <h2 class="h4 fw-bold mb-2" id="regional-areas-heading">Regional service areas</h2>
                        <p class="text-muted mb-0">Select a location to view its available packages.</p>
                    </div>
                    <div class="row row-cols-2 row-cols-md-3 row-cols-lg-5 g-3">
                        <?php foreach ($regionalServiceAreas as $area): ?>
                            <div class="col">
                                <a href="?region=<?= rawurlencode($area) ?>#regional-packages"
                                    class="d-block bg-white border rounded-4 h-100 px-3 py-3 fw-semibold text-decoration-none <?= $selectedRegion === $area ? 'border-primary text-primary' : 'text-dark' ?>"
                                    <?= $selectedRegion === $area ? 'aria-current="true"' : '' ?>>
                                    <i class="bi bi-geo-alt text-primary me-2" aria-hidden="true"></i><?= htmlspecialchars($area, ENT_QUOTES, 'UTF-8') ?>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>

                <?php if ($selectedRegion !== null): ?>
                    <section aria-labelledby="selected-region-heading">
                        <div class="mb-4">
                            <span class="text-primary fw-bold small text-uppercase tracking-wide">Regional packages</span>
                            <h2 class="h3 fw-bold mt-2" id="selected-region-heading"><?= htmlspecialchars($selectedRegion, ENT_QUOTES, 'UTF-8') ?> Packages</h2>
                        </div>
                        <?php foreach ($categoryPackages as $category => $packages): ?>
                            <section class="mb-5" aria-labelledby="regional-<?= strtolower($category) ?>-heading">
                                <h3 class="display-6 fw-bold mb-4" id="regional-<?= strtolower($category) ?>-heading">
                                    <?= htmlspecialchars($category, ENT_QUOTES, 'UTF-8') ?>
                                </h3>
                                <div class="row g-4">
                                    <?php renderPackageCards($packages, $category, $selectedRegion, $cartToken); ?>
                                </div>
                            </section>
                        <?php endforeach; ?>
                    </section>
                <?php endif; ?>
            </div>

            <div class="tab-pane fade" id="basic-panel" role="tabpanel" aria-labelledby="basic-tab" tabindex="0">
                <div class="mb-4">
                    <span class="text-primary fw-bold small text-uppercase tracking-wide">Choose Basic Package</span>
                    <h2 class="h3 fw-bold mt-2">Basic Packages</h2>
                </div>
                <div class="row g-4">
                    <?php renderPackageCards($categoryPackages['Basic'], 'Basic', '', $cartToken); ?>
                </div>
            </div>

            <div class="tab-pane fade" id="standard-panel" role="tabpanel" aria-labelledby="standard-tab" tabindex="0">
                <div class="mb-4">
                    <span class="text-primary fw-bold small text-uppercase tracking-wide">Choose Standard Package</span>
                    <h2 class="h3 fw-bold mt-2">Standard Packages</h2>
                </div>
                <div class="row g-4">
                    <?php renderPackageCards($categoryPackages['Standard'], 'Standard', '', $cartToken); ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
