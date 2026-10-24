<?php

require_once __DIR__ . '/../includes/admin-auth.php';
requireAdminLogin();

if ($pdo === null) {
    http_response_code(503);
    exit('Pending orders are unavailable because the database is not connected.');
}

$migrationStatement = $pdo->prepare(
    'SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?'
);
$migrationStatement->execute(['order_documents']);
if ((int) $migrationStatement->fetchColumn() === 0) {
    http_response_code(503);
    $adminPageTitle = 'Pending orders';
    require __DIR__ . '/_header.php';
    ?>
    <div class="alert alert-warning" role="alert">
        <h2 class="h5 alert-heading">Pending-order update required</h2>
        <p class="mb-0">Apply <code>database/migrations/20261024_pending_order_review.sql</code> to the configured MySQL database, then reload this page.</p>
    </div>
    <?php
    require __DIR__ . '/_footer.php';
    exit;
}

$error = null;
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $orderIdValue = $_POST['order_id'] ?? '';
    $action = $_POST['action'] ?? '';
    if (!validateAdminCsrf()) {
        http_response_code(400);
        $error = 'Your session expired. Refresh the page and try again.';
    } elseif (!is_string($orderIdValue) || !ctype_digit($orderIdValue) || (int) $orderIdValue < 1) {
        http_response_code(400);
        $error = 'That order could not be found.';
    } elseif (!is_string($action) || !in_array($action, ['approve', 'decline'], true)) {
        http_response_code(400);
        $error = 'Choose approve or decline for this order.';
    } else {
        $orderId = (int) $orderIdValue;
        $newStatus = $action === 'approve' ? 'confirmed' : 'cancelled';
        $pdo->beginTransaction();
        $currentStatement = $pdo->prepare(
            'SELECT payment_status, order_status FROM orders WHERE id = ? FOR UPDATE'
        );
        $currentStatement->execute([$orderId]);
        $currentOrder = $currentStatement->fetch();
        if (!is_array($currentOrder)) {
            $pdo->rollBack();
            http_response_code(404);
            $error = 'That order could not be found.';
        } elseif ($currentOrder['payment_status'] !== 'paid' || $currentOrder['order_status'] !== 'new') {
            $pdo->rollBack();
            http_response_code(409);
            $error = 'This order has already been reviewed or is not paid.';
        } else {
            $pdo->prepare('UPDATE orders SET order_status = ? WHERE id = ?')->execute([$newStatus, $orderId]);
            $pdo->prepare(
                'INSERT INTO order_events (order_id, admin_user_id, event_type, details) VALUES (?, ?, ?, ?)'
            )->execute([
                $orderId,
                (int) $_SESSION['admin_user_id'],
                $action === 'approve' ? 'order_approved' : 'order_declined',
                $currentOrder['order_status'] . ' -> ' . $newStatus,
            ]);
            $pdo->commit();
            header('Location: pending.php?updated=' . $action, true, 303);
            exit;
        }
    }
}

$notice = match ($_GET['updated'] ?? '') {
    'approve' => 'Order approved and confirmed.',
    'decline' => 'Order declined. Because payment was already collected, issue any required refund separately in Square.',
    default => null,
};

$orders = $pdo->query(
    "SELECT o.id, o.order_number, o.customer_first_name, o.customer_last_name, o.customer_phone,
            o.address, o.city, o.postal_code, o.preferred_date, o.preferred_time,
    o.instructor_preference, o.course_date, o.total_cents, o.paid_at, o.created_at,
            front.id AS front_document_id, back.id AS back_document_id
     FROM orders o
     LEFT JOIN order_documents front ON front.order_id = o.id AND front.document_side = 'front'
     LEFT JOIN order_documents back ON back.order_id = o.id AND back.document_side = 'back'
     WHERE o.payment_status = 'paid' AND o.order_status = 'new'
     ORDER BY o.paid_at ASC, o.id ASC
     LIMIT 100"
)->fetchAll();

$adminPageTitle = 'Pending orders';
require __DIR__ . '/_header.php';
?>
<?php if ($notice !== null): ?><div class="alert alert-success" role="status"><?= adminEscape($notice) ?></div><?php endif; ?>
<?php if ($error !== null): ?><div class="alert alert-danger" role="alert"><?= adminEscape($error) ?></div><?php endif; ?>
<section class="admin-panel">
    <div class="admin-panel-heading">
        <div>
            <h2 class="h5 fw-bold mb-1">Paid orders awaiting review</h2>
            <p class="text-secondary small mb-0">Review the student details and both learner permit photos before approving or declining.</p>
        </div>
        <span class="admin-status admin-status-pending"><?= count($orders) ?> pending</span>
    </div>
    <div class="alert alert-info small" role="note">
        Approving confirms the order. Declining marks it cancelled; it does not automatically refund the payment. Handle any refund separately in Square.
    </div>
    <div class="table-responsive">
        <table class="table admin-orders-table admin-pending-table align-middle mb-0">
            <thead>
                <tr>
                    <th>SL</th><th>Invoice no.</th><th>Student name</th><th>Number</th><th>Status</th>
                    <th>Address</th><th>Preferred schedule</th><th>Images</th><th>Amount</th><th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($orders === []): ?>
                <tr><td class="text-center py-5 text-secondary" colspan="10">There are no paid orders awaiting review.</td></tr>
            <?php else: foreach ($orders as $index => $order): ?>
                <tr>
                    <td><?= $index + 1 ?></td>
                    <td><strong><?= adminEscape($order['order_number']) ?></strong><small><?= adminEscape(date('d/m/Y g:i A', strtotime($order['paid_at'] ?? $order['created_at']))) ?></small></td>
                    <td><?= adminEscape($order['customer_first_name'] . ' ' . $order['customer_last_name']) ?></td>
                    <td><a href="tel:<?= adminEscape($order['customer_phone']) ?>"><?= adminEscape($order['customer_phone']) ?></a></td>
                    <td><span class="admin-status admin-status-pending">Pending review</span></td>
                    <td><?= adminEscape($order['address'] . ', ' . $order['city'] . ' ' . $order['postal_code']) ?></td>
                    <td>
                        <?= $order['preferred_date'] ? adminEscape(date('M j, Y', strtotime($order['preferred_date']))) : 'Not specified' ?>
                        <small><?= adminEscape(ucfirst($order['preferred_time'] ?: 'Time not specified')) ?><?= $order['instructor_preference'] ? ' · ' . adminEscape($order['instructor_preference']) : '' ?></small>
                        <?php if ($order['course_date']): ?><small>5-hour course: <?= adminEscape(date('M j, Y', strtotime($order['course_date']))) ?></small><?php endif; ?>
                    </td>
                    <td>
                        <div class="admin-permit-images">
                            <?php foreach (['front' => $order['front_document_id'], 'back' => $order['back_document_id']] as $side => $documentId): ?>
                                <?php if ($documentId !== null): ?>
                                    <a href="permit-image.php?id=<?= (int) $documentId ?>" target="_blank" rel="noopener noreferrer" aria-label="View learner permit <?= adminEscape($side) ?> photo">
                                        <img src="permit-image.php?id=<?= (int) $documentId ?>" alt="Permit <?= adminEscape($side) ?>" loading="lazy">
                                        <span><?= adminEscape(ucfirst($side)) ?></span>
                                    </a>
                                <?php else: ?>
                                    <span class="text-secondary small"><?= adminEscape(ucfirst($side)) ?> missing</span>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </td>
                    <td class="fw-bold">$<?= number_format(((int) $order['total_cents']) / 100, 2) ?></td>
                    <td>
                        <form method="post" action="pending.php" class="admin-pending-actions">
                            <input type="hidden" name="csrf_token" value="<?= adminEscape(adminCsrfToken()) ?>">
                            <input type="hidden" name="order_id" value="<?= (int) $order['id'] ?>">
                            <button class="btn btn-sm btn-success" type="submit" name="action" value="approve">Approve</button>
                            <button class="btn btn-sm btn-outline-danger" type="submit" name="action" value="decline" onclick="return confirm('Decline this paid order? Any refund must be handled separately in Square.')">Decline</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
    <p class="admin-table-note mb-0">Showing up to 100 oldest paid orders awaiting review. Permit photos are served only to authenticated administrators.</p>
</section>
<?php require __DIR__ . '/_footer.php'; ?>
