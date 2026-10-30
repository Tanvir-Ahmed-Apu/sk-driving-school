<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'includes/package-catalog.php';

$productIdValue = $_GET['id'] ?? '';
$productId = is_string($productIdValue) && ctype_digit($productIdValue) ? (int) $productIdValue : 0;
$product = $productsById[$productId] ?? null;
$productError = null;
$remainingQuantity = 99;
$quantityValue = '1';

if ($product === null) {
    http_response_code($productCatalogError === null ? 404 : 503);
} else {
    $cartToken = $_SESSION['cart_token'] ?? '';
    if (!is_string($cartToken) || $cartToken === '') {
        $cartToken = bin2hex(random_bytes(32));
        $_SESSION['cart_token'] = $cartToken;
    }

    $cart = isset($_SESSION['cart']) && is_array($_SESSION['cart']) ? $_SESSION['cart'] : [];
    $itemId = $product['region'] . '|' . $product['category'] . '|' . $productId;
    $currentQuantity = isset($cart[$itemId]) && is_int($cart[$itemId]) && $cart[$itemId] > 0
        ? min(99, $cart[$itemId])
        : 0;
    $remainingQuantity = 99 - $currentQuantity;

    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
        $submittedToken = $_POST['cart_token'] ?? '';
        $submittedProductId = $_POST['product_id'] ?? '';

        if (
            ($_POST['action'] ?? '') !== 'add_to_cart'
            || !is_string($submittedToken)
            || !hash_equals($cartToken, $submittedToken)
            || !is_string($submittedProductId)
            || !ctype_digit($submittedProductId)
            || (int) $submittedProductId !== $productId
        ) {
            http_response_code(400);
            $productError = 'We could not add this product. Refresh the page and try again.';
        } else {
            $submittedQuantity = $_POST['quantity'] ?? '';
            $quantityValue = is_string($submittedQuantity) ? $submittedQuantity : '';
            $quantity = is_string($quantityValue) && ctype_digit($quantityValue) ? (int) $quantityValue : 0;
            if ($remainingQuantity === 0) {
                $productError = 'You already have the maximum quantity of this product in your cart.';
            } elseif ($quantity < 1 || $quantity > $remainingQuantity) {
                $productError = 'Choose a quantity between 1 and ' . $remainingQuantity . '.';
            } else {
                $cart[$itemId] = $currentQuantity + $quantity;
                $_SESSION['cart'] = $cart;
                $_SESSION['cart_notice'] = $quantity . ' × ' . $product['name'] . ' added to your cart.';
                header('Location: cart');
                exit;
            }
        }
    }
}

$pageTitle = $product !== null
    ? $product['name'] . ' | SK Driving School'
    : 'Product unavailable | SK Driving School';
include 'includes/header.php';
?>
<header class="site-header">
    <?php
    require_once 'includes/topbar.php';
    require_once 'includes/navbar.php';
    ?>
</header>

<main>
    <section class="py-5 bg-white border-bottom">
        <div class="container py-3">
            <nav aria-label="Breadcrumb" class="mb-4">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="packages">Packages</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Product details</li>
                </ol>
            </nav>
            <?php if ($product === null): ?>
                <h1 class="display-5 fw-bold">Product unavailable</h1>
                <p class="lead text-muted mb-4">
                    <?= htmlspecialchars($productCatalogError ?? 'This product could not be found or is no longer available.', ENT_QUOTES, 'UTF-8') ?>
                </p>
                <a class="btn btn-primary rounded-pill px-4" href="packages">Browse packages</a>
            <?php else: ?>
                <span class="text-primary fw-bold small text-uppercase tracking-wide">
                    <?= htmlspecialchars($product['category'], ENT_QUOTES, 'UTF-8') ?> package
                </span>
                <h1 class="display-5 fw-bold mt-2 mb-2"><?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?></h1>
                <?php if ($product['region'] !== ''): ?>
                    <p class="text-muted mb-0"><i class="bi bi-geo-alt me-1" aria-hidden="true"></i><?= htmlspecialchars($product['region'], ENT_QUOTES, 'UTF-8') ?></p>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </section>

    <?php if ($product !== null): ?>
        <section class="py-5 bg-light">
            <div class="container">
                <?php if ($productError !== null): ?>
                    <div class="alert alert-danger" role="alert"><?= htmlspecialchars($productError, ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>
                <div class="row g-4 align-items-start">
                    <div class="col-lg-7">
                        <article class="card border-0 shadow-sm rounded-4 p-4 p-md-5">
                            <h2 class="h3 fw-bold mb-3">What’s included</h2>
                            <?php if (isset($product['subtitle'])): ?>
                                <p class="text-muted"><?= htmlspecialchars($product['subtitle'], ENT_QUOTES, 'UTF-8') ?></p>
                            <?php endif; ?>
                            <ul class="list-unstyled mb-0">
                                <?php foreach ($product['features'] as $feature): ?>
                                    <li class="d-flex gap-2 mb-3">
                                        <i class="bi bi-check-circle-fill text-primary" aria-hidden="true"></i>
                                        <span><?= htmlspecialchars($feature, ENT_QUOTES, 'UTF-8') ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </article>
                    </div>
                    <div class="col-lg-5">
                        <aside class="card border-0 shadow-sm rounded-4 p-4" aria-label="Product price and order">
                            <span class="text-muted small">Package price</span>
                            <p class="display-6 fw-bold mb-4">$<?= number_format($product['price'], 2) ?></p>
                            <form method="post" action="product?id=<?= $productId ?>">
                                <input type="hidden" name="action" value="add_to_cart">
                                <input type="hidden" name="product_id" value="<?= $productId ?>">
                                <input type="hidden" name="cart_token" value="<?= htmlspecialchars($cartToken, ENT_QUOTES, 'UTF-8') ?>">
                                <label for="product-quantity" class="form-label fw-semibold">Quantity</label>
                                <input
                                    class="form-control mb-3"
                                    type="number"
                                    id="product-quantity"
                                    name="quantity"
                                    min="1"
                                    max="<?= $remainingQuantity ?>"
                                    value="<?= htmlspecialchars((string) $quantityValue, ENT_QUOTES, 'UTF-8') ?>"
                                    required
                                    <?= $remainingQuantity === 0 ? 'disabled' : '' ?>
                                >
                                <button type="submit" class="btn btn-primary w-100 rounded-pill py-3 fw-semibold" <?= $remainingQuantity === 0 ? 'disabled' : '' ?>>
                                    <i class="bi bi-cart-plus me-2" aria-hidden="true"></i>Add to cart
                                </button>
                            </form>
                            <p class="small text-muted mt-3 mb-0">
                                <?= $remainingQuantity === 99
                                    ? 'Choose how many packages to add. Up to 99 of each product can be in your cart.'
                                    : 'You can add up to ' . $remainingQuantity . ' more of this product. You’ll review your selections in the cart before proceeding.' ?>
                            </p>
                        </aside>
                    </div>
                </div>
                <a href="packages" class="d-inline-block mt-4 text-decoration-none">
                    <i class="bi bi-arrow-left me-2" aria-hidden="true"></i>Back to packages
                </a>
            </div>
        </section>
    <?php endif; ?>
</main>

<?php include 'includes/footer.php'; ?>
