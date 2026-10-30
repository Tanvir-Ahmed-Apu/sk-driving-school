<?php

require_once __DIR__ . '/includes/db.php';

$token = $_GET['ref'] ?? '';
$order = false;
if ($pdo !== null && is_string($token) && preg_match('/\A[a-f0-9]{64}\z/', $token) === 1) {
    $statement = $pdo->prepare(
        'SELECT order_number, payment_status, order_status FROM orders WHERE public_token = ? LIMIT 1'
    );
    $statement->execute([$token]);
    $order = $statement->fetch();
}

require_once __DIR__ . '/includes/header.php';
?>
<main class="container py-5">
    <?php if (!is_array($order)): ?>
        <section class="card border-0 shadow-sm rounded-4 p-4 p-md-5 text-center">
            <h1 class="h3 fw-bold">We couldn’t find that order</h1>
            <p class="text-muted">Please contact our team if you completed payment and need help.</p>
            <a class="btn btn-primary rounded-pill align-self-center" href="contact.php">Contact the school</a>
        </section>
    <?php else: ?>
        <section class="card border-0 shadow-sm rounded-4 p-4 p-md-5 text-center">
            <span class="text-primary display-5"><i class="bi bi-check-circle"></i></span>
            <h1 class="h3 fw-bold mt-3">Thank you for your order</h1>
            <p class="text-muted">Order <strong><?= htmlspecialchars($order['order_number'], ENT_QUOTES, 'UTF-8') ?></strong></p>
            <?php if ($order['payment_status'] === 'paid'): ?>
                <div class="alert alert-success mx-auto" role="status">Sk-driving-school confirmed your payment. Your order is awaiting our team’s review; we’ll follow up to confirm your schedule.</div>
            <?php else: ?>
                <div class="alert alert-info mx-auto" role="status">We’re waiting for Square to confirm the payment. This page does not treat a return from checkout as proof of payment.</div>
                <a class="btn btn-outline-primary rounded-pill align-self-center" href="">Refresh payment status</a>
            <?php endif; ?>
            <a href="index" class="text-decoration-none">Return to home</a>
        </section>
    <?php endif; ?>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
