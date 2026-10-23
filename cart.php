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

$cart = isset($_SESSION['cart']) && is_array($_SESSION['cart']) ? $_SESSION['cart'] : [];
$cartError = null;
$cartNotice = null;

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $submittedToken = $_POST['cart_token'] ?? '';
    $action = $_POST['action'] ?? '';
    $itemId = $_POST['item_id'] ?? '';

    if (!is_string($submittedToken) || !hash_equals($cartToken, $submittedToken)) {
        http_response_code(400);
        $cartError = 'Your cart could not be updated. Refresh the page and try again.';
    } elseif ($action === 'clear_cart') {
        $_SESSION['cart'] = [];
        header('Location: cart.php');
        exit;
    } elseif (is_string($itemId) && array_key_exists($itemId, $cart)) {
        if ($action === 'remove_item') {
            unset($cart[$itemId]);
            $_SESSION['cart'] = $cart;
            header('Location: cart.php');
            exit;
        }

        if ($action === 'update_quantity') {
            $quantityValue = $_POST['quantity'] ?? '';
            $quantity = is_string($quantityValue) && ctype_digit($quantityValue) ? (int) $quantityValue : 0;
            if ($quantity >= 1 && $quantity <= 99) {
                $cart[$itemId] = $quantity;
                $_SESSION['cart'] = $cart;
                header('Location: cart.php');
                exit;
            }
            $cartError = 'Choose a quantity between 1 and 99.';
        } else {
            http_response_code(400);
            $cartError = 'That cart action is not available.';
        }
    } else {
        http_response_code(400);
        $cartError = 'That cart item could not be found. Refresh the page and try again.';
    }
}

$cartLines = [];
$cartTotal = 0;
$cartCount = 0;
foreach ($cart as $itemId => $quantity) {
    if (!is_string($itemId) || !is_int($quantity) || $quantity < 1 || $quantity > 99) {
        error_log('Invalid item data found in the shopping cart session.');
        $cartError = 'A cart item could not be displayed. Please remove it or clear your cart and try again.';
        continue;
    }

    $parts = explode('|', $itemId);
    if (count($parts) !== 3) {
        error_log('Invalid package identifier found in the shopping cart session.');
        $cartError = 'A cart item could not be displayed. Please remove it or clear your cart and try again.';
        continue;
    }

    [$region, $category, $indexValue] = $parts;
    $packageIndex = ctype_digit($indexValue) ? (int) $indexValue : -1;
    if (
        ($region !== '' && !in_array($region, $regionalServiceAreas, true))
        || !isset($packagesByCategory[$category][$packageIndex])
    ) {
        error_log('Unknown package identifier found in the shopping cart session.');
        $cartError = 'A cart item could not be displayed. Please remove it or clear your cart and try again.';
        continue;
    }

    $package = $packagesByCategory[$category][$packageIndex];
    $lineTotal = $package['price'] * $quantity;
    $cartLines[] = [
        'id' => $itemId,
        'category' => $category,
        'region' => $region,
        'name' => $package['name'],
        'price' => $package['price'],
        'quantity' => $quantity,
        'line_total' => $lineTotal,
    ];
    $cartTotal += $lineTotal;
    $cartCount += $quantity;
}

require_once 'includes/header.php';
?>
<header class="site-header">
    <?php
    require_once 'includes/topbar.php';
    require_once 'includes/navbar.php';
    ?>
</header>

<main class="cart-page">
    <section class="cart-hero py-5">
        <div class="container py-3">
            <span class="checkout-eyebrow"><i class="bi bi-cart3 me-2" aria-hidden="true"></i>Your selections</span>
            <h1 class="display-5 fw-bold mt-3 mb-2">Shopping cart</h1>
            <p class="lead text-muted mb-0">Review the driving packages you’ve added before contacting our team.</p>
        </div>
    </section>

    <section class="py-5">
        <div class="container">
            <?php if ($cartError !== null): ?>
                <div class="alert alert-danger" role="alert"><?= htmlspecialchars($cartError, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>
            <?php if ($cartNotice !== null): ?>
                <div class="alert alert-success" role="status"><?= htmlspecialchars($cartNotice, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <?php if ($cartLines === []): ?>
                <section class="cart-empty-card text-center p-4 p-md-5" aria-labelledby="empty-cart-heading">
                    <span class="cart-empty-icon"><i class="bi bi-cart-x" aria-hidden="true"></i></span>
                    <h2 class="h3 fw-bold mt-4" id="empty-cart-heading">Your cart is empty</h2>
                    <p class="text-muted mx-auto mb-4">Browse the Basic, Standard, or regional packages and add the options you’d like to discuss.</p>
                    <a href="packages.php" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold">
                        Browse driving packages <i class="bi bi-arrow-right ms-2" aria-hidden="true"></i>
                    </a>
                </section>
            <?php else: ?>
                <div class="row g-4 align-items-start">
                    <div class="col-lg-8">
                        <section class="cart-items-card p-3 p-md-4" aria-labelledby="cart-items-heading">
                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                                <h2 class="h4 fw-bold mb-0" id="cart-items-heading">Selected packages</h2>
                                <span class="badge rounded-pill bg-light text-dark border"><?= $cartCount ?> <?= $cartCount === 1 ? 'item' : 'items' ?></span>
                            </div>

                            <?php foreach ($cartLines as $lineIndex => $line): ?>
                                <article class="cart-line">
                                    <div class="cart-line-info">
                                        <span class="cart-category"><?= htmlspecialchars($line['category'], ENT_QUOTES, 'UTF-8') ?> package</span>
                                        <h3 class="h5 fw-bold mb-1"><?= htmlspecialchars($line['name'], ENT_QUOTES, 'UTF-8') ?></h3>
                                        <?php if ($line['region'] !== ''): ?>
                                            <p class="small text-muted mb-1"><i class="bi bi-geo-alt me-1" aria-hidden="true"></i><?= htmlspecialchars($line['region'], ENT_QUOTES, 'UTF-8') ?></p>
                                        <?php endif; ?>
                                        <p class="small text-muted mb-0">$<?= number_format($line['price'], 2) ?> each</p>
                                    </div>
                                    <div class="cart-line-actions">
                                        <form method="post" action="cart.php" class="cart-quantity-form">
                                            <input type="hidden" name="cart_token" value="<?= htmlspecialchars($cartToken, ENT_QUOTES, 'UTF-8') ?>">
                                            <input type="hidden" name="action" value="update_quantity">
                                            <input type="hidden" name="item_id" value="<?= htmlspecialchars($line['id'], ENT_QUOTES, 'UTF-8') ?>">
                                            <label class="visually-hidden" for="quantity-<?= (int) $lineIndex ?>">Quantity for <?= htmlspecialchars($line['name'], ENT_QUOTES, 'UTF-8') ?></label>
                                            <input class="form-control form-control-sm" type="number" id="quantity-<?= (int) $lineIndex ?>" min="1" max="99" name="quantity" value="<?= $line['quantity'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-primary" aria-label="Update quantity for <?= htmlspecialchars($line['name'], ENT_QUOTES, 'UTF-8') ?>">Update</button>
                                        </form>
                                        <strong class="cart-line-total">$<?= number_format($line['line_total'], 2) ?></strong>
                                        <form method="post" action="cart.php">
                                            <input type="hidden" name="cart_token" value="<?= htmlspecialchars($cartToken, ENT_QUOTES, 'UTF-8') ?>">
                                            <input type="hidden" name="action" value="remove_item">
                                            <input type="hidden" name="item_id" value="<?= htmlspecialchars($line['id'], ENT_QUOTES, 'UTF-8') ?>">
                                            <button type="submit" class="btn btn-link btn-sm text-danger p-0">Remove</button>
                                        </form>
                                    </div>
                                </article>
                            <?php endforeach; ?>

                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mt-4">
                                <a href="packages.php" class="cart-continue-link"><i class="bi bi-arrow-left me-2" aria-hidden="true"></i>Continue browsing</a>
                                <form method="post" action="cart.php">
                                    <input type="hidden" name="cart_token" value="<?= htmlspecialchars($cartToken, ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="hidden" name="action" value="clear_cart">
                                    <button type="submit" class="btn btn-link btn-sm text-muted p-0">Clear cart</button>
                                </form>
                            </div>
                        </section>
                    </div>

                    <div class="col-lg-4">
                        <aside class="cart-summary p-4" aria-labelledby="cart-summary-heading">
                            <h2 class="h4 fw-bold mb-4" id="cart-summary-heading">Cart summary</h2>
                            <div class="cart-summary-row">
                                <span>Items</span>
                                <strong><?= $cartCount ?></strong>
                            </div>
                            <div class="cart-summary-row">
                                <span>Subtotal</span>
                                <strong>$<?= number_format($cartTotal, 2) ?></strong>
                            </div>
                            <div class="cart-summary-total">
                                <span>Estimated total</span>
                                <strong>$<?= number_format($cartTotal, 2) ?></strong>
                            </div>
                            <p class="small text-muted mt-3 mb-4">Final pricing and availability are confirmed by our team. Your cart does not reserve lessons or charge a payment.</p>
                            <a href="checkout.php" class="btn btn-primary w-100 rounded-pill py-3 fw-bold">
                                Proceed to checkout <i class="bi bi-arrow-right ms-2" aria-hidden="true"></i>
                            </a>
                            <a href="tel:+16464000666" class="cart-call-link"><i class="bi bi-telephone me-2" aria-hidden="true"></i>Call or text (646) 400-0666</a>
                        </aside>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php include 'includes/footer.php'; ?>
