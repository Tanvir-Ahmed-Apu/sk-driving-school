<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'includes/package-catalog.php';
require_once 'includes/db.php';
require_once 'includes/square.php';

$cart = isset($_SESSION['cart']) && is_array($_SESSION['cart']) ? $_SESSION['cart'] : [];
$checkoutItems = [];
$basePrice = 0;
$cartCount = 0;
foreach ($cart as $itemId => $quantity) {
    if (!is_string($itemId) || !is_int($quantity) || $quantity < 1 || $quantity > 99) {
        error_log('Invalid item data found in the cart while opening checkout.');
        continue;
    }

    $parts = explode('|', $itemId);
    if (count($parts) !== 3) {
        error_log('Invalid package identifier found in the cart while opening checkout.');
        continue;
    }

    [$region, $category, $indexValue] = $parts;
    $productId = ctype_digit($indexValue) ? (int) $indexValue : 0;
    if (
        ($region !== '' && !in_array($region, $regionalServiceAreas, true))
        || !isset($productsById[$productId])
        || $productsById[$productId]['category'] !== $category
        || $productsById[$productId]['region'] !== $region
    ) {
        error_log('Unknown package identifier found in the cart while opening checkout.');
        continue;
    }

    $package = $productsById[$productId];
    $lineTotal = $package['price'] * $quantity;
    $checkoutItems[] = [
        'name' => $package['name'],
        'category' => $category,
        'region' => $region,
        'quantity' => $quantity,
        'price' => $package['price'],
        'line_total' => $lineTotal,
    ];
    $basePrice += $lineTotal;
    $cartCount += $quantity;
}

require_once 'includes/checkout-submit.php';

function checkoutFormValue(string $field): string
{
    $value = $_POST[$field] ?? '';
    return is_string($value) ? htmlspecialchars($value, ENT_QUOTES, 'UTF-8') : '';
}

require_once 'includes/header.php';

$minimumRoadTestDate = checkoutMinimumRoadTestDate();
$squareService = squarePaymentService();
$squareApplicationId = $squareService->getApplicationId();
$squareLocationId = $squareService->getLocationId();
$squareEnvironment = $squareService->getEnvironment();
$squarePaymentConfigured = $squareApplicationId !== ''
    && $squareLocationId !== ''
    && $squareService->getClient() instanceof \Square\SquareClient;
$squareSdkUrl = $squareEnvironment === 'production'
    ? 'https://web.squarecdn.com/v1/square.js'
    : 'https://sandbox.web.squarecdn.com/v1/square.js';
?>
<header class="site-header">
    <?php
    require_once 'includes/topbar.php';
    require_once 'includes/navbar.php';
    ?>
</header>

<main class="checkout-page">
    <section class="checkout-hero py-4">
        <div class="container">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div>
                    <span class="checkout-eyebrow"><i class="bi bi-car-front me-2" aria-hidden="true"></i>Secure package checkout</span>
                    <h1 class="h2 fw-bold mt-2 mb-1">Plan your booking</h1>
                    <p class="text-muted mb-0">Enter your details, review your order, and pay securely through Square.</p>
                </div>
                <a class="checkout-help-link" href="tel:+16464000666">
                    <span class="checkout-help-icon"><i class="bi bi-telephone" aria-hidden="true"></i></span>
                    <span><small class="d-block text-muted">Questions? Call or text</small><strong>(646) 400-0666</strong></span>
                </a>
            </div>
        </div>
    </section>

    <section class="py-4 py-lg-5">
        <div class="container">
            <?php if ($checkoutError !== null): ?>
                <div class="alert alert-danger mb-4" role="alert"><?= htmlspecialchars($checkoutError, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>
            <form method="post" action="checkout.php" id="checkout-form">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($checkoutCsrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="checkout_token" value="<?= htmlspecialchars($checkoutSubmissionToken, ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="payment_attempt_id" value="<?= htmlspecialchars($checkoutPaymentAttemptId, ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="square_card_token" id="square-card-token">
            <div class="row g-4 align-items-start">
                <div class="col-lg-7 order-2 order-lg-1">
                    <section class="checkout-card p-4 p-md-5 mb-4" aria-labelledby="billing-heading">
                        <div class="checkout-section-heading mb-4">
                            <span class="checkout-section-icon"><i class="bi bi-person-vcard" aria-hidden="true"></i></span>
                            <div>
                                <h2 class="h4 fw-bold mb-1" id="billing-heading">Billing details</h2>
                                <p class="text-muted small mb-0">These details are saved with your order so our team can confirm your service.</p>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-sm-6">
                                <label class="form-label fw-semibold" for="billing-first-name">First name</label>
                                <input type="text" class="form-control" id="billing-first-name" name="first_name" maxlength="100" autocomplete="given-name" value="<?= checkoutFormValue('first_name') ?>" required>
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label fw-semibold" for="billing-last-name">Last name</label>
                                <input type="text" class="form-control" id="billing-last-name" name="last_name" maxlength="100" autocomplete="family-name" value="<?= checkoutFormValue('last_name') ?>" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold" for="billing-email">Email address</label>
                                <input type="email" class="form-control" id="billing-email" name="email" maxlength="254" autocomplete="email" value="<?= checkoutFormValue('email') ?>" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold" for="billing-phone">Phone number</label>
                                <input type="tel" class="form-control" id="billing-phone" name="phone" maxlength="40" autocomplete="tel" value="<?= checkoutFormValue('phone') ?>" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold" for="billing-address">Street address</label>
                                <input type="text" class="form-control" id="billing-address" name="address" maxlength="255" autocomplete="street-address" value="<?= checkoutFormValue('address') ?>" required>
                            </div>
                            <div class="col-sm-7">
                                <label class="form-label fw-semibold" for="billing-city">City</label>
                                <input type="text" class="form-control" id="billing-city" name="city" maxlength="120" autocomplete="address-level2" value="<?= checkoutFormValue('city') ?>" required>
                            </div>
                            <div class="col-sm-5">
                                <label class="form-label fw-semibold" for="billing-zip">ZIP code</label>
                                <input type="text" class="form-control" id="billing-zip" name="postal_code" maxlength="20" autocomplete="postal-code" value="<?= checkoutFormValue('postal_code') ?>" required>
                            </div>
                        </div>
                    </section>

                    <section class="checkout-card p-4 p-md-5 mb-4" aria-labelledby="service-heading">
                        <div class="d-flex flex-column flex-sm-row justify-content-between gap-3">
                            <div>
                                <span class="badge bg-primary-subtle text-primary rounded-pill mb-3">Your cart</span>
                                <h2 class="h4 fw-bold mb-2" id="service-heading">Selected packages</h2>
                                <p class="text-muted mb-0">Review the packages and quantities you added to your cart.</p>
                            </div>
                            <div class="checkout-item-price text-sm-end">
                                <span class="h3 fw-bold mb-0">$<?= number_format($basePrice, 2) ?></span>
                                <small class="d-block text-muted">Cart subtotal</small>
                            </div>
                        </div>

                        <?php if ($checkoutItems !== []): ?>
                            <div class="checkout-selected-items mt-4">
                                <?php foreach ($checkoutItems as $item): ?>
                                    <div class="checkout-selected-item">
                                        <div>
                                            <strong><?= htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8') ?></strong>
                                            <small>
                                                <?= htmlspecialchars($item['category'], ENT_QUOTES, 'UTF-8') ?> package
                                                <?php if ($item['region'] !== ''): ?>
                                                    · <?= htmlspecialchars($item['region'], ENT_QUOTES, 'UTF-8') ?>
                                                <?php endif; ?>
                                                · Qty <?= $item['quantity'] ?>
                                            </small>
                                        </div>
                                        <strong>$<?= number_format($item['line_total'], 2) ?></strong>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <a href="cart" class="small fw-semibold text-decoration-none d-inline-flex align-items-center mt-3">
                                <i class="bi bi-pencil-square me-2" aria-hidden="true"></i>Edit cart
                            </a>
                        <?php else: ?>
                            <div class="checkout-preview-notice mt-4" role="status">
                                <i class="bi bi-cart-x" aria-hidden="true"></i>
                                <p class="mb-0">Your cart is empty. <a href="packages" class="fw-semibold">Browse packages</a> and add a package before proceeding.</p>
                            </div>
                        <?php endif; ?>

                        <fieldset class="checkout-instructor-picker mt-4" <?= $checkoutItems === [] ? 'disabled' : '' ?>>
                            <legend class="form-label fw-semibold mb-3">Instructor preference <span class="text-muted fw-normal">(optional)</span></legend>
                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <input class="btn-check" type="radio" name="instructor" id="instructor-rajandrasinh" value="rajandrasinh" <?= checkoutFormValue('instructor') === 'rajandrasinh' ? 'checked' : '' ?>>
                                    <label class="checkout-instructor-option" for="instructor-rajandrasinh">
                                        <img src="https://www.skdrivingschoolny.com/images/instructor/Image_Rajandrasinh.jpg" alt="" loading="lazy">
                                        <span class="checkout-instructor-details">
                                            <span class="checkout-instructor-name">RAJANDRASINH, B CHAVDA</span>
                                            <small>Certified Instructor</small>
                                            <span class="checkout-instructor-select"><i class="bi bi-check2" aria-hidden="true"></i><span>Select instructor</span></span>
                                        </span>
                                    </label>
                                </div>
                                <div class="col-sm-6">
                                    <input class="btn-check" type="radio" name="instructor" id="instructor-mohammed" value="mohammed" <?= checkoutFormValue('instructor') === 'mohammed' ? 'checked' : '' ?>>
                                    <label class="checkout-instructor-option" for="instructor-mohammed">
                                        <img src="https://www.skdrivingschoolny.com/images/instructor/Image_Alam.jpeg" alt="" loading="lazy">
                                        <span class="checkout-instructor-details">
                                            <span class="checkout-instructor-name">MOHAMMED, M ALAM</span>
                                            <small>Certified Instructor</small>
                                            <span class="checkout-instructor-select"><i class="bi bi-check2" aria-hidden="true"></i><span>Select instructor</span></span>
                                        </span>
                                    </label>
                                </div>
                            </div>
                            <div class="form-text mt-2">Instructor requests depend on availability and will be confirmed by our team.</div>
                        </fieldset>

                    </section>

                    <section class="checkout-card p-4 p-md-5" aria-labelledby="appointment-heading">
                        <div class="checkout-section-heading mb-4">
                            <span class="checkout-section-icon"><i class="bi bi-calendar2-week" aria-hidden="true"></i></span>
                            <div>
                                <h2 class="h4 fw-bold mb-1" id="appointment-heading">Road test preferences</h2>
                                <p class="text-muted small mb-0">These are preferences only. We’ll confirm availability with you.</p>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold" for="road-test-date">Preferred road test date</label>
                            <input type="date" id="road-test-date" name="road_test_date" class="form-control form-control-lg" min="<?= htmlspecialchars($minimumRoadTestDate, ENT_QUOTES, 'UTF-8') ?>" value="<?= checkoutFormValue('road_test_date') ?>">
                            <div class="form-text">Choose a future date (New York time). Your request is not a confirmed DMV appointment.</div>
                        </div>

                        <div class="mb-4">
                            <span class="form-label fw-semibold d-block">Preferred time</span>
                            <input type="hidden" name="time" value="11:00 AM">
                            <span class="checkout-class-time"><i class="bi bi-clock" aria-hidden="true"></i>Morning · 11:00 AM</span>
                            <div class="form-text">The available preferred time is 11:00 AM. Our team will confirm your booking.</div>
                        </div>

                    </section>
                </div>

                <div class="col-lg-5 order-1 order-lg-2">
                    <aside class="checkout-summary p-4 p-md-4" aria-labelledby="summary-heading">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h2 class="h4 fw-bold mb-0" id="summary-heading">Order summary</h2>
                            <span class="badge rounded-pill bg-light text-dark border" id="checkout-item-count" aria-live="polite" data-base-count="<?= $cartCount ?>"><?= $cartCount ?> <?= $cartCount === 1 ? 'item' : 'items' ?></span>
                        </div>

                        <?php foreach ($checkoutItems as $item): ?>
                            <div class="checkout-summary-item">
                                <span>
                                    <?= htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8') ?> × <?= $item['quantity'] ?>
                                    <?php if ($item['region'] !== ''): ?>
                                        <small class="d-block text-muted"><?= htmlspecialchars($item['region'], ENT_QUOTES, 'UTF-8') ?></small>
                                    <?php endif; ?>
                                </span>
                                <strong>$<?= number_format($item['line_total'], 2) ?></strong>
                            </div>
                        <?php endforeach; ?>
                        <div class="checkout-summary-item checkout-subtotal">
                            <span>Subtotal</span>
                            <strong id="checkout-subtotal" aria-live="polite">$<?= number_format($basePrice, 2) ?></strong>
                        </div>
                        <div class="checkout-summary-item text-muted">
                            <span>Items</span>
                            <span><?= $cartCount ?></span>
                        </div>
                        <div class="checkout-summary-item text-muted">
                            <span>Sales tax</span>
                            <span>Calculated if applicable</span>
                        </div>
                        <div class="checkout-total">
                            <span>Estimated total</span>
                            <strong id="checkout-total" aria-live="polite" aria-atomic="true">$<?= number_format($basePrice, 2) ?></strong>
                        </div>
                        <p class="checkout-summary-disclaimer">Final service pricing may depend on your road test location. We’ll confirm the details before your appointment.</p>

                        <div class="checkout-payment-panel mt-4">
                            <div class="checkout-card-payment">
                                <div class="checkout-section-heading mb-3">
                                    <span class="checkout-section-icon"><i class="bi bi-credit-card-2-front" aria-hidden="true"></i></span>
                                    <div>
                                        <h3 class="h5 fw-bold mb-1">Credit or debit card</h3>
                                        <p class="text-muted small mb-0">Secure payment powered by Square</p>
                                    </div>
                                </div>
                                <?php if ($squarePaymentConfigured): ?>
                                    <div
                                        id="square-card-container"
                                        class="square-card-container"
                                        data-application-id="<?= htmlspecialchars($squareApplicationId, ENT_QUOTES, 'UTF-8') ?>"
                                        data-location-id="<?= htmlspecialchars($squareLocationId, ENT_QUOTES, 'UTF-8') ?>"
                                    ></div>
                                    <p class="small text-muted mt-2 mb-0">
                                        Card details are encrypted by Square and never stored on this website.
                                    </p>
                                <?php else: ?>
                                    <div class="alert alert-warning small mb-0" role="alert">
                                        Square card payments are not configured yet. Check the application ID, access token, location ID, and environment.
                                    </div>
                                <?php endif; ?>
                                <div id="square-payment-message" class="small mt-3" role="status" aria-live="polite"></div>
                            </div>
                            <div class="form-check checkout-terms-check mt-3">
                                <input class="form-check-input" type="checkbox" id="checkout-terms" name="terms" value="1" required <?= checkoutFormValue('terms') === '1' ? 'checked' : '' ?>>
                                <label class="form-check-label small" for="checkout-terms">
                                    By placing an order, you accept our
                                    <a href="terms-and-condition" target="_blank" rel="noopener noreferrer">Terms &amp; Conditions</a>.
                                </label>
                            </div>
                            <button type="submit" id="checkout-submit-button" class="btn btn-primary w-100 rounded-pill py-3 mt-3 fw-bold" <?= $checkoutItems === [] || !$squarePaymentConfigured ? 'disabled' : '' ?>>
                                Pay securely <i class="bi bi-lock-fill ms-2" aria-hidden="true"></i>
                            </button>
                            <p class="small text-muted text-center mt-3 mb-0">Your order will appear for review after Square confirms payment.</p>
                        </div>

                        <div class="checkout-trust-row">
                            <span><i class="bi bi-patch-check-fill" aria-hidden="true"></i> NY DMV School #11644</span>
                            <span><i class="bi bi-shield-lock-fill" aria-hidden="true"></i> Your information stays private</span>
                        </div>
                    </aside>

                    <aside class="checkout-campus-card mt-4 p-4" aria-labelledby="campus-heading">
                        <h2 class="h6 fw-bold mb-3" id="campus-heading"><i class="bi bi-geo-alt text-primary me-2" aria-hidden="true"></i>Queens Main Campus · Room 204</h2>
                        <address class="small text-muted mb-3">60-15 Roosevelt Ave, 2nd Fl<br>Woodside, Queens, NY 11377</address>
                        <p class="small text-muted mb-3"><i class="bi bi-clock me-2" aria-hidden="true"></i>Office: Mon–Sat 7 am–10 pm · Sun 10 am–5 pm<br><span class="ms-4">Driving lessons: daily by appointment, 7 am–9 pm</span></p>
                        <p class="small text-muted mb-0">Free pickup and drop-off may be available within a 10-minute drive of our office. <a href="contact" class="fw-semibold text-decoration-none">Ask us about your location.</a></p>
                    </aside>
                </div>
                </form>
            </div>
        </div>
    </section>
</main>

<?php if ($squarePaymentConfigured): ?>
<script src="<?= htmlspecialchars($squareSdkUrl, ENT_QUOTES, 'UTF-8') ?>"></script>
<?php endif; ?>
<script src="assets/js/square-checkout.js"></script>
<script>
    document.querySelectorAll('input[name="instructor"]').forEach((instructor) => {
        instructor.addEventListener('change', () => {
            document.querySelectorAll('.checkout-instructor-select span:last-child').forEach((label) => {
                label.textContent = 'Select instructor';
            });
            const selectedLabel = document.querySelector(`label[for="${instructor.id}"] .checkout-instructor-select span:last-child`);
            selectedLabel.textContent = 'Selected';
        });
    });
</script>

<?php include 'includes/footer.php'; ?>
