<?php
require_once __DIR__ . '/includes/db.php';
require_once 'includes/header.php';

$messageSent = false;
$formError = null;
$formValues = [
    'name' => '',
    'phone' => '',
    'email' => '',
    'message' => '',
];

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    foreach ($formValues as $field => $default) {
        $formValues[$field] = isset($_POST[$field]) && is_string($_POST[$field])
            ? trim($_POST[$field])
            : $default;
    }

    if (
        $formValues['name'] === ''
        || $formValues['phone'] === ''
        || !filter_var($formValues['email'], FILTER_VALIDATE_EMAIL)
        || $formValues['message'] === ''
    ) {
        $formError = 'Please complete all fields and enter a valid email address.';
    } elseif ($pdo !== null) {
        try {
            $stmt = $pdo->prepare(
                'INSERT INTO contact_messages (name, phone, email, subject, message) VALUES (?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $formValues['name'],
                $formValues['phone'],
                $formValues['email'],
                'Consultation request',
                $formValues['message'],
            ]);
            $messageSent = true;
            $formValues = array_fill_keys(array_keys($formValues), '');
        } catch (PDOException $e) {
            error_log('Contact form submission failed: ' . $e->getMessage());
            $formError = 'We could not submit your request right now. Please call or email us directly.';
        }
    } elseif (saveContactSubmission($formValues)) {
        $messageSent = true;
        $formValues = array_fill_keys(array_keys($formValues), '');
    } else {
        $formError = 'We could not submit your request right now. Please call or email us directly.';
        error_log('Contact form submission failed: database connection is unavailable and local fallback storage failed.');
    }
}

function contactFormValue(array $formValues, string $field): string
{
    return htmlspecialchars($formValues[$field], ENT_QUOTES, 'UTF-8');
}
?>
<header class="site-header">
    <?php
    require_once 'includes/topbar.php';
    require_once 'includes/navbar.php';
    ?>
</header>

<main>
    <section class="contact-hero py-5">
        <div class="container py-lg-4">
            <div class="row align-items-center g-4">
                <div class="col-lg-8">
                    <span class="badge bg-white text-primary border rounded-pill px-3 py-2 mb-3">
                        <i class="bi bi-patch-check-fill me-1" aria-hidden="true"></i> NY DMV Approved School #11644
                    </span>
                    <h1 class="display-4 fw-bold lh-sm mb-3">Let’s get you ready for the road.</h1>
                    <p class="lead text-muted mb-0">Schedule an assessment, ask a question, or plan your road test with our Woodside team.</p>
                </div>
                <div class="col-lg-4">
                    <div class="contact-response-card">
                        <span class="contact-detail-icon"><i class="bi bi-chat-dots" aria-hidden="true"></i></span>
                        <div>
                            <p class="small text-muted mb-1">A real person will get back to you</p>
                            <p class="fw-bold mb-0">Reply within 30 minutes during operating hours</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5 bg-light">
        <div class="container">
            <div class="row g-4 align-items-stretch">
                <div class="col-lg-7">
                    <section class="card contact-form-card border-0 shadow-sm rounded-4 h-100 p-4 p-md-5" aria-labelledby="consultation-heading">
                        <div class="mb-4">
                            <span class="text-primary fw-bold small text-uppercase tracking-wide">We’re here to help</span>
                            <h2 class="h2 fw-bold mt-2 mb-2" id="consultation-heading">Schedule an Assessment or Consultation</h2>
                            <p class="text-muted mb-0">Tell us a little about what you need and our scheduling concierge will be in touch.</p>
                        </div>

                        <?php if ($messageSent): ?>
                            <div class="alert alert-success rounded-3" role="status">
                                <i class="bi bi-check-circle-fill me-2" aria-hidden="true"></i>Your request has been received. We’ll contact you soon.
                            </div>
                        <?php endif; ?>

                        <?php if ($formError !== null): ?>
                            <div class="alert alert-danger rounded-3" role="alert">
                                <i class="bi bi-exclamation-circle-fill me-2" aria-hidden="true"></i><?= htmlspecialchars($formError, ENT_QUOTES, 'UTF-8') ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="contact.php">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold" for="contact-name">Student Full Name</label>
                                    <input type="text" id="contact-name" name="name" class="form-control form-control-lg" placeholder="Your full name" autocomplete="name" value="<?= contactFormValue($formValues, 'name') ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold" for="contact-phone">Mobile Phone <span class="text-muted fw-normal">(SMS confirmations)</span></label>
                                    <input type="tel" id="contact-phone" name="phone" class="form-control form-control-lg" placeholder="(646) 400-0666" autocomplete="tel" value="<?= contactFormValue($formValues, 'phone') ?>" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold" for="contact-email">Email Address</label>
                                    <input type="email" id="contact-email" name="email" class="form-control form-control-lg" placeholder="you@example.com" autocomplete="email" value="<?= contactFormValue($formValues, 'email') ?>" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold" for="contact-message">Special Requests or Target Road Test Date</label>
                                    <textarea id="contact-message" name="message" class="form-control" rows="5" placeholder="Tell us how we can help..." required><?= contactFormValue($formValues, 'message') ?></textarea>
                                </div>
                                <div class="col-12 pt-2">
                                    <button type="submit" class="btn btn-primary btn-lg rounded-pill w-100 fw-bold py-3">
                                        Request a Consultation <i class="bi bi-arrow-right ms-2" aria-hidden="true"></i>
                                    </button>
                                    <p class="text-center text-muted small mt-3 mb-0">
                                        <i class="bi bi-shield-lock me-1" aria-hidden="true"></i>Your details are kept private and never shared with third parties.
                                    </p>
                                </div>
                            </div>
                        </form>
                    </section>
                </div>

                <div class="col-lg-5">
                    <div class="contact-campus-card card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                        <div class="p-4 p-md-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2">Queens Main Campus</span>
                                <span class="small fw-bold text-muted">ROOM 204</span>
                            </div>
                            <h2 class="h4 fw-bold mb-2">Woodside Training Center</h2>
                            <p class="text-muted small mb-4">DMV testing simulators, a classroom lecture theater, and dedicated instructor dual-control dispatch bays.</p>

                            <div class="contact-info-row">
                                <span class="contact-detail-icon"><i class="bi bi-geo-alt" aria-hidden="true"></i></span>
                                <div>
                                    <span class="small text-muted d-block mb-1">Visit us</span>
                                    <address class="fw-semibold mb-0">60-15 Roosevelt Ave, 2nd Fl<br>Woodside, Queens, NY 11377</address>
                                </div>
                            </div>
                            <div class="contact-info-row">
                                <span class="contact-detail-icon"><i class="bi bi-telephone" aria-hidden="true"></i></span>
                                <div>
                                    <span class="small text-muted d-block mb-1">Hotline & text messages</span>
                                    <a class="fw-semibold text-decoration-none" href="tel:+16464000666">(646) 400-0666</a>
                                    <span class="small text-muted d-block">Text message only for SMS inquiries</span>
                                </div>
                            </div>
                            <div class="contact-info-row mb-0">
                                <span class="contact-detail-icon"><i class="bi bi-envelope" aria-hidden="true"></i></span>
                                <div>
                                    <span class="small text-muted d-block mb-1">Email us</span>
                                    <a class="fw-semibold text-decoration-none text-break" href="mailto:skdrivingschoolny@gmail.com">skdrivingschoolny@gmail.com</a>
                                </div>
                            </div>
                        </div>

                        <div class="contact-map-wrapper">
                            <iframe
                                src="https://maps.google.com/maps?q=60-15%20Roosevelt%20Ave%2C%202nd%20Fl%2C%20Woodside%2C%20Queens%2C%20NY%2011377&amp;z=16&amp;output=embed"
                                title="Map to SK Driving School Woodside Training Center at 60-15 Roosevelt Avenue, Woodside, Queens"
                                loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade"
                                allowfullscreen>
                            </iframe>
                        </div>
                        <div class="px-4 py-3 bg-white">
                            <a href="https://www.google.com/maps/search/?api=1&amp;query=60-15%20Roosevelt%20Ave%2C%202nd%20Fl%2C%20Woodside%2C%20Queens%2C%20NY%2011377" target="_blank" rel="noopener noreferrer" class="btn btn-outline-primary rounded-pill w-100 fw-semibold">
                                <i class="bi bi-map me-2" aria-hidden="true"></i>Get Directions
                            </a>
                        </div>
                    </div>

                    <div class="contact-note-card rounded-4 p-4">
                        <i class="bi bi-info-circle me-2" aria-hidden="true"></i>
                        <span class="fw-semibold">Need a quick answer?</span>
                        <span class="d-block small mt-1">Call or text our desk line at <a class="text-white fw-bold" href="tel:+16464000666">(646) 400-0666</a>.</span>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include 'includes/footer.php'; ?>
