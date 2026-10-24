<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/includes/db.php';

$result = 'error';
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $email = isset($_POST['email']) && is_string($_POST['email']) ? trim($_POST['email']) : '';
    $token = isset($_POST['csrf_token']) && is_string($_POST['csrf_token']) ? $_POST['csrf_token'] : '';
    if (
        isset($_SESSION['newsletter_csrf'])
        && is_string($_SESSION['newsletter_csrf'])
        && hash_equals($_SESSION['newsletter_csrf'], $token)
        && isset($_POST['consent'])
        && $_POST['consent'] === '1'
        && filter_var($email, FILTER_VALIDATE_EMAIL)
        && strlen($email) <= 254
        && $pdo !== null
    ) {
        try {
            $statement = $pdo->prepare(
                "INSERT INTO newsletter_subscriptions (email, status)
                 VALUES (?, 'subscribed')
                 ON DUPLICATE KEY UPDATE status = 'subscribed', unsubscribed_at = NULL"
            );
            $statement->execute([$email]);
            $result = 'success';
        } catch (PDOException $e) {
            error_log('Newsletter subscription failed: ' . $e->getMessage());
        }
    } elseif ($pdo === null) {
        $result = 'unavailable';
    } else {
        http_response_code(422);
        $result = 'invalid';
    }
} else {
    http_response_code(405);
    header('Allow: POST');
}
require_once __DIR__ . '/includes/header.php';
?>
<main class="container py-5">
    <section class="card border-0 shadow-sm rounded-4 p-4 p-md-5 text-center">
        <?php if ($result === 'success'): ?>
            <span class="text-primary display-5"><i class="bi bi-envelope-check"></i></span>
            <h1 class="h3 fw-bold mt-3">You’re subscribed</h1>
            <p class="text-muted">Thanks for signing up for occasional driving tips and school updates.</p>
        <?php elseif ($result === 'invalid'): ?>
            <h1 class="h3 fw-bold">Check your email address</h1>
            <p class="text-muted">Enter a valid email address and submit the form again.</p>
        <?php else: ?>
            <h1 class="h3 fw-bold">Subscription unavailable</h1>
            <p class="text-muted">We could not save your subscription right now. Please try again later.</p>
        <?php endif; ?>
        <a href="index.php" class="btn btn-primary rounded-pill align-self-center mt-2">Return to home</a>
    </section>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
