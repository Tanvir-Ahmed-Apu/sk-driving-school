<?php

require_once __DIR__ . '/../includes/admin-auth.php';
requireAdminLogin();

if ($pdo === null) {
    http_response_code(503);
    exit('Package sales are unavailable because the database is not connected.');
}

$documentsTableStatement = $pdo->prepare(
    'SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?'
);
$documentsTableStatement->execute(['order_documents']);
if ((int) $documentsTableStatement->fetchColumn() === 0) {
    http_response_code(503);
    $adminPageTitle = 'Package sales';
    require __DIR__ . '/_header.php';
    ?>
    <div class="alert alert-warning" role="alert">
        <h2 class="h5 alert-heading">Package-sales update required</h2>
        <p class="mb-0">Apply <code>database/migrations/20261024_pending_order_review.sql</code> to the configured MySQL database, then reload this page.</p>
    </div>
    <?php
    require __DIR__ . '/_footer.php';
    exit;
}

$search = isset($_GET['q']) && is_string($_GET['q']) ? trim($_GET['q']) : '';
$conditions = ["o.payment_status = 'paid'"];
$parameters = [];
if ($search !== '') {
    $conditions[] = '(o.order_number LIKE ? OR o.customer_first_name LIKE ? OR o.customer_last_name LIKE ? OR o.customer_phone LIKE ?)';
    $query = '%' . $search . '%';
    array_push($parameters, $query, $query, $query, $query);
}

$statement = $pdo->prepare(
    "SELECT o.id, o.order_number, o.customer_first_name, o.customer_last_name, o.customer_phone,
            o.address, o.city, o.postal_code, o.preferred_date, o.preferred_time,
            o.instructor_preference, o.course_date, o.order_status, o.total_cents,
            o.discount_cents, o.paid_at, o.created_at,
            front.id AS front_document_id, back.id AS back_document_id
     FROM orders o
     LEFT JOIN order_documents front ON front.order_id = o.id AND front.document_side = 'front'
     LEFT JOIN order_documents back ON back.order_id = o.id AND back.document_side = 'back'
     WHERE " . implode(' AND ', $conditions) . '
     ORDER BY COALESCE(o.paid_at, o.created_at) DESC, o.id DESC
     LIMIT 100'
);
$statement->execute($parameters);
$orders = $statement->fetchAll();
$itemsByOrder = [];
if ($orders !== []) {
    $orderIds = array_map(static fn (array $order): int => (int) $order['id'], $orders);
    $placeholders = implode(',', array_fill(0, count($orderIds), '?'));
    $itemsStatement = $pdo->prepare(
        "SELECT order_id, item_name, category, region, quantity
         FROM order_items
         WHERE order_id IN ($placeholders)
         ORDER BY order_id, id"
    );
    $itemsStatement->execute($orderIds);
    foreach ($itemsStatement->fetchAll() as $item) {
        $itemsByOrder[(int) $item['order_id']][] = $item;
    }
}

$adminPageTitle = 'Package sales';
require __DIR__ . '/_header.php';
?>
<section class="admin-panel">
    <div class="admin-panel-heading">
        <div>
            <h2 class="h5 fw-bold mb-1">Paid package orders</h2>
            <p class="text-secondary small mb-0">Review student and payment details, voucher discounts, and permit photos for completed payments.</p>
        </div>
        <form class="admin-search" method="get" action="package-sales.php">
            <label class="visually-hidden" for="package-sales-search">Search paid orders</label>
            <input class="form-control" type="search" id="package-sales-search" name="q" value="<?= adminEscape($search) ?>" placeholder="Search invoice, student, or number">
            <button class="btn btn-primary" type="submit">Search</button>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table admin-orders-table admin-pending-table align-middle mb-0">
            <thead>
                <tr>
                    <th>SL</th><th>Invoice no.</th><th>Student name</th><th>Number</th><th>Status</th>
                    <th>Address</th><th>Preferred schedule</th><th>Images</th><th>Amount</th><th>Voucher</th><th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($orders === []): ?>
                <tr><td class="text-center py-5 text-secondary" colspan="11">No paid package orders found.</td></tr>
            <?php else: foreach ($orders as $index => $order): ?>
                <tr>
                    <td><?= $index + 1 ?></td>
                    <td><strong><?= adminEscape($order['order_number']) ?></strong><small><?= adminEscape(date('d/m/Y g:i A', strtotime($order['paid_at'] ?? $order['created_at']))) ?></small></td>
                    <td><?= adminEscape($order['customer_first_name'] . ' ' . $order['customer_last_name']) ?></td>
                    <td><a href="tel:<?= adminEscape($order['customer_phone']) ?>"><?= adminEscape($order['customer_phone']) ?></a></td>
                    <td><span class="admin-status admin-status-<?= adminEscape($order['order_status']) ?>"><?= adminEscape(ucfirst(str_replace('_', ' ', $order['order_status']))) ?></span></td>
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
                        <?php if ((int) $order['discount_cents'] > 0): ?>
                            −$<?= number_format(((int) $order['discount_cents']) / 100, 2) ?>
                        <?php else: ?>
                            —
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if (!empty($itemsByOrder[(int) $order['id']])): ?>
                            <?php foreach ($itemsByOrder[(int) $order['id']] as $item): ?>
                                <small>
                                    <?= adminEscape($item['item_name']) ?> × <?= (int) $item['quantity'] ?>
                                    <?php if ($item['region'] !== null && $item['region'] !== ''): ?>
                                        · <?= adminEscape($item['region']) ?>
                                    <?php endif; ?>
                                </small>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <span class="text-secondary small">No package details</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
    <p class="admin-table-note mb-0">Showing up to 100 most recent paid orders. Action lists the purchased packages and quantities; permit images are available only to signed-in administrators.</p>
</section>
<?php require __DIR__ . '/_footer.php'; ?>
