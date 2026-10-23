<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'includes/package-catalog.php';

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
    $packageIndex = ctype_digit($indexValue) ? (int) $indexValue : -1;
    if (
        ($region !== '' && !in_array($region, $regionalServiceAreas, true))
        || !isset($packagesByCategory[$category][$packageIndex])
    ) {
        error_log('Unknown package identifier found in the cart while opening checkout.');
        continue;
    }

    $package = $packagesByCategory[$category][$packageIndex];
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

require_once 'includes/header.php';

$today = date('Y-m-d');
$coursePrice = 50;
$certificateDiscount = 30;
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
                    <span class="checkout-eyebrow"><i class="bi bi-car-front me-2" aria-hidden="true"></i>Road test checkout preview</span>
                    <h1 class="h2 fw-bold mt-2 mb-1">Plan your booking</h1>
                    <p class="text-muted mb-0">Choose your preferences and review estimated pricing before contacting our team.</p>
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
            <div class="row g-4 align-items-start">
                <div class="col-lg-7 order-2 order-lg-1">
                    <section class="checkout-card p-4 p-md-5 mb-4" aria-labelledby="billing-heading">
                        <div class="checkout-section-heading mb-4">
                            <span class="checkout-section-icon"><i class="bi bi-person-vcard" aria-hidden="true"></i></span>
                            <div>
                                <h2 class="h4 fw-bold mb-1" id="billing-heading">Billing details</h2>
                                <p class="text-muted small mb-0">Complete these fields for your reference before contacting us.</p>
                            </div>
                        </div>

                        <div class="checkout-preview-notice mb-4" role="note">
                            <i class="bi bi-info-circle" aria-hidden="true"></i>
                            <p class="mb-0">This checkout is a preview. Information entered here is not sent or saved. To make a booking, use the contact form or call us.</p>
                        </div>

                        <div class="row g-3">
                            <div class="col-sm-6">
                                <label class="form-label fw-semibold" for="billing-first-name">First name</label>
                                <input type="text" class="form-control" id="billing-first-name" name="first_name" autocomplete="given-name">
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label fw-semibold" for="billing-last-name">Last name</label>
                                <input type="text" class="form-control" id="billing-last-name" name="last_name" autocomplete="family-name">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold" for="billing-email">Email address</label>
                                <input type="email" class="form-control" id="billing-email" name="email" autocomplete="email">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold" for="billing-phone">Phone number</label>
                                <input type="tel" class="form-control" id="billing-phone" name="phone" autocomplete="tel">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold" for="billing-address">Street address</label>
                                <input type="text" class="form-control" id="billing-address" name="address" autocomplete="street-address">
                            </div>
                            <div class="col-sm-7">
                                <label class="form-label fw-semibold" for="billing-city">City</label>
                                <input type="text" class="form-control" id="billing-city" name="city" autocomplete="address-level2">
                            </div>
                            <div class="col-sm-5">
                                <label class="form-label fw-semibold" for="billing-zip">ZIP code</label>
                                <input type="text" class="form-control" id="billing-zip" name="postal_code" inputmode="numeric" autocomplete="postal-code">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold" for="permit-id">Learner permit ID number <span class="text-muted fw-normal">(optional)</span></label>
                                <input type="text" class="form-control" id="permit-id" autocomplete="off" disabled>
                                <div class="form-text">For your privacy, this preview does not collect permit numbers. We’ll explain what is needed and how to share it securely.</div>
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
                            <a href="cart.php" class="small fw-semibold text-decoration-none d-inline-flex align-items-center mt-3">
                                <i class="bi bi-pencil-square me-2" aria-hidden="true"></i>Edit cart
                            </a>
                        <?php else: ?>
                            <div class="checkout-preview-notice mt-4" role="status">
                                <i class="bi bi-cart-x" aria-hidden="true"></i>
                                <p class="mb-0">Your cart is empty. <a href="packages.php" class="fw-semibold">Browse packages</a> and add a package before proceeding.</p>
                            </div>
                        <?php endif; ?>

                        <fieldset class="checkout-instructor-picker mt-4" <?= $checkoutItems === [] ? 'disabled' : '' ?>>
                            <legend class="form-label fw-semibold mb-3">Instructor preference <span class="text-muted fw-normal">(optional)</span></legend>
                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <input class="btn-check" type="radio" name="instructor" id="instructor-rajandrasinh" value="rajandrasinh">
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
                                    <input class="btn-check" type="radio" name="instructor" id="instructor-mohammed" value="mohammed">
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

                        <div class="checkout-addon mt-4">
                            <div class="form-check form-switch d-flex align-items-start justify-content-between gap-3 p-0">
                                <div>
                                    <label class="form-check-label fw-bold" for="prelicensing-course">Add the 5-Hour Pre-Licensing Course</label>
                                    <p class="text-muted small mb-0 mt-1">Add this if you still need the NY DMV MV-278 certificate. Bring your learner permit photo ID or DMV state ID and paper learner permit. The usual class time is 11:00 am, Monday–Saturday.</p>
                                    <a href="faq.php" class="small fw-semibold text-decoration-none">Read course FAQs <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
                                </div>
                                <input class="form-check-input fs-4 flex-shrink-0 mt-1" type="checkbox" role="switch" id="prelicensing-course" aria-describedby="course-price">
                            </div>
                            <span class="checkout-addon-price" id="course-price">+$<?= number_format($coursePrice, 2) ?></span>
                        </div>

                        <label class="checkout-certificate-option mt-3" for="has-certificate">
                            <input class="form-check-input mt-1" type="checkbox" id="has-certificate">
                            <span>
                                <strong>I already have a valid 5-hour course certificate</strong>
                                <small class="d-block text-muted">Request the $<?= number_format($certificateDiscount, 2) ?> certificate discount. It can’t be combined with purchasing the course above; our team will confirm eligibility.</small>
                            </span>
                        </label>
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
                            <input type="date" id="road-test-date" name="road_test_date" class="form-control form-control-lg" min="<?= htmlspecialchars($today, ENT_QUOTES, 'UTF-8') ?>">
                            <div class="form-text">Choose a future date. Your request is not a confirmed DMV appointment.</div>
                        </div>

                        <fieldset class="mb-4">
                            <legend class="form-label fw-semibold">Preferred time of day</legend>
                            <div class="row g-2">
                                <div class="col-4">
                                    <input type="radio" class="btn-check" name="time" id="morning" value="morning" checked>
                                    <label class="checkout-time-option" for="morning"><i class="bi bi-sunrise" aria-hidden="true"></i><span>Morning</span><small>7 am–12 pm</small></label>
                                </div>
                                <div class="col-4">
                                    <input type="radio" class="btn-check" name="time" id="afternoon" value="afternoon">
                                    <label class="checkout-time-option" for="afternoon"><i class="bi bi-sun" aria-hidden="true"></i><span>Afternoon</span><small>12 pm–5 pm</small></label>
                                </div>
                                <div class="col-4">
                                    <input type="radio" class="btn-check" name="time" id="evening" value="evening">
                                    <label class="checkout-time-option" for="evening"><i class="bi bi-sunset" aria-hidden="true"></i><span>Evening</span><small>5 pm–9 pm</small></label>
                                </div>
                            </div>
                        </fieldset>

                        <div class="checkout-note">
                            <i class="bi bi-info-circle-fill" aria-hidden="true"></i>
                            <p class="mb-0">Rush road test appointments within one week may be available for an additional <strong>$50</strong>. Ask our team to confirm appointment availability and fees.</p>
                        </div>

                        <div class="checkout-permit-upload mt-4">
                            <label class="form-label fw-semibold" for="permit-upload">Learner permit document</label>
                            <input class="form-control" type="file" id="permit-upload" accept="image/*,.pdf" disabled aria-describedby="permit-upload-note">
                            <div class="form-text" id="permit-upload-note">Secure document upload is not available here yet. Please do not email or upload sensitive documents; our team will explain a secure option if needed.</div>
                        </div>

                        <div class="checkout-course-schedule mt-4" id="course-schedule" hidden>
                            <h3 class="h6 fw-bold mb-1">Preferred 5-hour class schedule <span class="text-muted fw-normal">(optional)</span></h3>
                            <p class="small text-muted mb-3">Choose a preferred date. Classes are typically scheduled Monday–Saturday at 11:00 am; our team will confirm availability.</p>
                            <div class="row g-3 align-items-end">
                                <div class="col-sm-7">
                                    <label class="form-label fw-semibold" for="course-date">Preferred class date</label>
                                    <input type="date" class="form-control" id="course-date" name="course_date" min="<?= htmlspecialchars($today, ENT_QUOTES, 'UTF-8') ?>">
                                </div>
                                <div class="col-sm-5">
                                    <span class="form-label fw-semibold d-block">Usual class time</span>
                                    <span class="checkout-class-time"><i class="bi bi-clock" aria-hidden="true"></i>11:00 am</span>
                                </div>
                            </div>
                            <p class="small text-muted mb-0 mt-3" id="course-date-note">Classes are usually Monday–Saturday. We’ll confirm the date and seat availability.</p>
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
                        <div class="checkout-summary-item" id="course-summary-row" hidden>
                            <span>5-Hour Pre-Licensing Course</span>
                            <strong>$<?= number_format($coursePrice, 2) ?></strong>
                        </div>
                        <div class="checkout-summary-item checkout-subtotal">
                            <span>Subtotal</span>
                            <strong id="checkout-subtotal" aria-live="polite">$<?= number_format($basePrice, 2) ?></strong>
                        </div>
                        <div class="checkout-summary-item" id="certificate-discount-row" hidden>
                            <span>5-hour certificate discount</span>
                            <strong>−$<?= number_format($certificateDiscount, 2) ?></strong>
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
                            <strong id="checkout-total" aria-live="polite" aria-atomic="true" data-base-price="<?= $basePrice ?>" data-course-price="<?= $coursePrice ?>" data-certificate-discount="<?= $certificateDiscount ?>">$<?= number_format($basePrice, 2) ?></strong>
                        </div>
                        <p class="checkout-summary-disclaimer">Final service pricing may depend on your road test location. We’ll confirm the details before your appointment.</p>

                        <div class="checkout-payment-panel mt-4">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <span class="checkout-section-icon"><i class="bi bi-credit-card" aria-hidden="true"></i></span>
                                <div>
                                    <h3 class="h6 fw-bold mb-1">Choose a payment preference</h3>
                                    <p class="small text-muted mb-0">Your selection is not submitted or charged online.</p>
                                </div>
                            </div>
                            <fieldset class="checkout-payment-methods mb-3">
                                <legend class="visually-hidden">Payment preference</legend>
                                <div class="row g-2">
                                    <div class="col-4">
                                        <input class="btn-check" type="radio" name="payment_method" id="payment-card" value="card" checked>
                                        <label class="checkout-payment-option" for="payment-card"><i class="bi bi-credit-card" aria-hidden="true"></i><span>Card</span></label>
                                    </div>
                                    <div class="col-4">
                                        <input class="btn-check" type="radio" name="payment_method" id="payment-zelle" value="zelle">
                                        <label class="checkout-payment-option" for="payment-zelle"><span class="checkout-payment-mark checkout-payment-mark-zelle">Z</span><span>Zelle</span></label>
                                    </div>
                                    <div class="col-4">
                                        <input class="btn-check" type="radio" name="payment_method" id="payment-cash-app" value="cash_app">
                                        <label class="checkout-payment-option" for="payment-cash-app"><span class="checkout-payment-mark checkout-payment-mark-cash">＄</span><span>Cash App</span></label>
                                    </div>
                                </div>
                            </fieldset>
                            <div class="checkout-demo-alert" role="status">
                                <i class="bi bi-info-circle-fill" aria-hidden="true"></i>
                                <p class="mb-0" id="payment-method-note">Online payments aren’t configured yet. Your card choice is only a preference; no payment will be taken here. Call or text <a href="tel:+16464000666">(646) 400-0666</a> to arrange your booking or ask about payment plans.</p>
                            </div>
                            <div id="card-container" class="checkout-card-entry mt-3" aria-label="Card details">
                                <span class="text-muted small"><i class="bi bi-lock me-2" aria-hidden="true"></i>Card entry will be available when online payments are set up.</span>
                            </div>
                            <div class="form-check checkout-terms-check mt-3">
                                <input class="form-check-input" type="checkbox" id="checkout-terms">
                                <label class="form-check-label small" for="checkout-terms">
                                    By placing an order, you accept our
                                    <a href="terms-and-condition.php" target="_blank" rel="noopener noreferrer">Terms &amp; Conditions</a>.
                                </label>
                            </div>
                            <button type="button" class="btn btn-primary w-100 rounded-pill py-3 mt-3 fw-bold" disabled aria-describedby="payment-setup-note">
                                <i class="bi bi-lock me-2" aria-hidden="true"></i>Place order unavailable
                            </button>
                            <p class="small text-muted text-center mt-3 mb-0" id="payment-setup-note">Checkout details aren’t saved or sent from this page yet. Secure billing, document upload, and payment processing must be configured before an order can be placed.</p>
                            <a class="checkout-contact-action" href="contact.php"><i class="bi bi-calendar2-check" aria-hidden="true"></i><span>Request a booking consultation</span><i class="bi bi-arrow-right ms-auto" aria-hidden="true"></i></a>
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
                        <p class="small text-muted mb-0">Free pickup and drop-off may be available within a 10-minute drive of our office. <a href="contact.php" class="fw-semibold text-decoration-none">Ask us about your location.</a></p>
                    </aside>
                </div>
            </div>
        </div>
    </section>
</main>

<script>
    const courseToggle = document.getElementById('prelicensing-course');
    const certificateToggle = document.getElementById('has-certificate');
    const totalDisplay = document.getElementById('checkout-total');
    const subtotalDisplay = document.getElementById('checkout-subtotal');
    const itemCountDisplay = document.getElementById('checkout-item-count');
    const courseSummaryRow = document.getElementById('course-summary-row');
    const certificateDiscountRow = document.getElementById('certificate-discount-row');
    const courseSchedule = document.getElementById('course-schedule');
    const courseDate = document.getElementById('course-date');
    const paymentMethodNote = document.getElementById('payment-method-note');
    const cardContainer = document.getElementById('card-container');
    const courseDateNote = document.getElementById('course-date-note');

    const updateCheckoutTotal = () => {
        const basePrice = Number(totalDisplay.dataset.basePrice);
        const coursePrice = Number(totalDisplay.dataset.coursePrice);
        const certificateDiscount = Number(totalDisplay.dataset.certificateDiscount);
        const baseCount = Number(itemCountDisplay.dataset.baseCount);
        const subtotal = basePrice + (courseToggle.checked ? coursePrice : 0);
        const discount = certificateToggle.checked ? certificateDiscount : 0;
        const total = Math.max(0, subtotal - discount);

        subtotalDisplay.textContent = `$${subtotal.toFixed(2)}`;
        totalDisplay.textContent = `$${total.toFixed(2)}`;
        const itemCount = baseCount + (courseToggle.checked ? 1 : 0);
        itemCountDisplay.textContent = `${itemCount} ${itemCount === 1 ? 'item' : 'items'}`;
        courseSummaryRow.hidden = !courseToggle.checked;
        certificateDiscountRow.hidden = !certificateToggle.checked;
        courseSchedule.hidden = !courseToggle.checked;
        courseDate.disabled = !courseToggle.checked;
        if (!courseToggle.checked) {
            courseDate.value = '';
            courseDateNote.textContent = 'Classes are usually Monday–Saturday. We’ll confirm the date and seat availability.';
        }
    };

    courseToggle.addEventListener('change', () => {
        if (courseToggle.checked) {
            certificateToggle.checked = false;
        }
        updateCheckoutTotal();
    });

    certificateToggle.addEventListener('change', () => {
        if (certificateToggle.checked) {
            courseToggle.checked = false;
        }
        updateCheckoutTotal();
    });

    document.querySelectorAll('input[name="payment_method"]').forEach((paymentMethod) => {
        paymentMethod.addEventListener('change', () => {
            const methodNames = { card: 'card', zelle: 'Zelle', cash_app: 'Cash App' };
            paymentMethodNote.firstChild.textContent = `Online payments aren’t configured yet. Your ${methodNames[paymentMethod.value]} choice is only a preference; no payment will be taken here. Call or text `;
            cardContainer.hidden = paymentMethod.value !== 'card';
        });
    });

    document.querySelectorAll('input[name="instructor"]').forEach((instructor) => {
        instructor.addEventListener('change', () => {
            document.querySelectorAll('.checkout-instructor-select span:last-child').forEach((label) => {
                label.textContent = 'Select instructor';
            });
            const selectedLabel = document.querySelector(`label[for="${instructor.id}"] .checkout-instructor-select span:last-child`);
            selectedLabel.textContent = 'Selected';
        });
    });

    courseDate.addEventListener('change', () => {
        if (!courseDate.value) {
            return;
        }

        const selectedDate = new Date(`${courseDate.value}T12:00:00`);
        const isSunday = selectedDate.getDay() === 0;
        courseDateNote.textContent = isSunday
            ? 'Sunday classes are not part of the usual schedule. Please choose another day or contact us to ask.'
            : 'Preference noted. Our team will confirm the date and seat availability.';
    });
</script>

<?php include 'includes/footer.php'; ?>
