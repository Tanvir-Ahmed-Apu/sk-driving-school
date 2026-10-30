<?php

require_once __DIR__ . '/../includes/admin-auth.php';
requireAdminLogin();

if ($pdo === null) {
    http_response_code(503);
    exit('Dashboard data is unavailable because the database is not connected.');
}

$requiredTables = ['orders', 'order_items', 'contact_messages', 'newsletter_subscriptions'];
$tablePlaceholders = implode(',', array_fill(0, count($requiredTables), '?'));
$tableStatement = $pdo->prepare(
    "SELECT TABLE_NAME
     FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME IN ({$tablePlaceholders})"
);
$tableStatement->execute($requiredTables);
$availableTables = $tableStatement->fetchAll(PDO::FETCH_COLUMN);
$missingTables = array_values(array_diff($requiredTables, $availableTables));
$hasPaidAt = false;
if (!in_array('orders', $missingTables, true)) {
    $columnStatement = $pdo->prepare(
        "SELECT COUNT(*)
         FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'orders' AND COLUMN_NAME = 'paid_at'"
    );
    $columnStatement->execute();
    $hasPaidAt = (int) $columnStatement->fetchColumn() > 0;
}

if ($missingTables !== [] || !$hasPaidAt) {
    http_response_code(503);
    $adminPageTitle = 'Dashboard';
    require __DIR__ . '/_header.php';
    ?>
    <div class="alert alert-warning" role="alert">
        <h2 class="h5 alert-heading">Dashboard database update required</h2>
        <p class="mb-2">The dashboard can’t find all of its required analytics tables or columns.</p>
        <p class="mb-2">Apply <code>database/migrations/20261024_dashboard_metrics.sql</code> to the configured MySQL database, then reload this page.</p>
        <?php if ($missingTables !== []): ?>
            <p class="small mb-0">Missing tables: <?= adminEscape(implode(', ', $missingTables)) ?><?= !$hasPaidAt ? '; missing orders.paid_at' : '' ?></p>
        <?php elseif (!$hasPaidAt): ?>
            <p class="small mb-0">Missing column: orders.paid_at</p>
        <?php endif; ?>
    </div>
    <?php
    require __DIR__ . '/_footer.php';
    exit;
}

$timezone = new DateTimeZone(date_default_timezone_get());
$currentMonth = new DateTimeImmutable('first day of this month', $timezone);
$chartStart = $currentMonth->modify('-11 months');
$chartEnd = $currentMonth->modify('+1 month');
$today = new DateTimeImmutable('today', $timezone);
$tomorrow = $today->modify('+1 day');
$currentDay = (int) $today->format('j');
$monthKey = $currentMonth->format('Y-m');

$totals = $pdo->query(
    "SELECT
        (SELECT COALESCE(SUM(oi.quantity), 0)
         FROM order_items oi JOIN orders o ON o.id = oi.order_id
         WHERE o.payment_status = 'paid' AND oi.category <> 'Course') AS packages_sold,
        (SELECT COALESCE(SUM(total_cents), 0)
         FROM orders WHERE payment_status = 'paid') AS total_sales_cents,
        (SELECT COUNT(*) FROM orders WHERE payment_status = 'paid') AS paid_orders"
)->fetch();

$contactCount = (int) $pdo->query('SELECT COUNT(*) FROM contact_messages')->fetchColumn();
$newsletterCount = (int) $pdo->query(
    "SELECT COUNT(*) FROM newsletter_subscriptions WHERE status = 'subscribed'"
)->fetchColumn();

$monthlyStatement = $pdo->prepare(
    "SELECT DATE_FORMAT(COALESCE(paid_at, updated_at), '%Y-%m') AS period,
            SUM(total_cents) AS sales_cents
     FROM orders
     WHERE payment_status = 'paid'
       AND COALESCE(paid_at, updated_at) >= :start_date
       AND COALESCE(paid_at, updated_at) < :end_date
     GROUP BY period
     ORDER BY period"
);
$monthlyStatement->execute([
    'start_date' => $chartStart->format('Y-m-d 00:00:00'),
    'end_date' => $chartEnd->format('Y-m-d 00:00:00'),
]);
$monthlyValues = [];
foreach ($monthlyStatement->fetchAll() as $row) {
    $monthlyValues[$row['period']] = (int) $row['sales_cents'];
}
$monthlyChart = [];
for ($monthOffset = 0; $monthOffset < 12; $monthOffset++) {
    $month = $chartStart->modify("+{$monthOffset} months");
    $key = $month->format('Y-m');
    $monthlyChart[] = [
        'label' => $month->format('M'),
        'full_label' => $month->format('F Y'),
        'sales_cents' => $monthlyValues[$key] ?? 0,
        'is_current' => $key === $monthKey,
    ];
}

$dailyStatement = $pdo->prepare(
    'SELECT DAY(COALESCE(paid_at, updated_at)) AS day_number, SUM(total_cents) AS sales_cents
     FROM orders
     WHERE payment_status = \'paid\'
       AND COALESCE(paid_at, updated_at) >= :start_date
       AND COALESCE(paid_at, updated_at) < :end_date
     GROUP BY day_number
     ORDER BY day_number'
);
$dailyStatement->execute([
    'start_date' => $currentMonth->format('Y-m-d 00:00:00'),
    'end_date' => $tomorrow->format('Y-m-d 00:00:00'),
]);
$dailyValues = [];
foreach ($dailyStatement->fetchAll() as $row) {
    $dailyValues[(int) $row['day_number']] = (int) $row['sales_cents'];
}
$dailyChart = [];
for ($dayNumber = 1; $dayNumber <= $currentDay; $dayNumber++) {
    $dailyChart[] = [
        'day' => $dayNumber,
        'sales_cents' => $dailyValues[$dayNumber] ?? 0,
    ];
}

$latestOrders = $pdo->query(
    "SELECT id, order_number, customer_first_name, customer_last_name, total_cents,
            payment_status, paid_at, updated_at
     FROM orders
     ORDER BY created_at DESC
     LIMIT 6"
)->fetchAll();
$monthSalesCents = array_sum(array_column($monthlyChart, 'sales_cents'));
$dailyMax = max(1, ...array_column($dailyChart, 'sales_cents'));
$monthlyMax = max(1, ...array_column($monthlyChart, 'sales_cents'));

$adminPageTitle = 'Dashboard';
require __DIR__ . '/_header.php';
?>
<section class="admin-dashboard-intro">
    <div>
        <p class="admin-eyebrow mb-1">Sales overview</p>
        <h2 class="h4 fw-bold mb-1">Welcome back</h2>
        <p class="text-secondary mb-0">A clear view of your package sales and customer activity.</p>
    </div>
    <div class="admin-period-chip"><i class="bi bi-calendar3"></i><?= adminEscape($today->format('F j, Y')) ?></div>
</section>

<section class="admin-stat-grid" aria-label="Business totals">
    <article class="admin-stat-card">
        <span class="admin-stat-icon icon-blue"><i class="bi bi-box-seam"></i></span>
        <span class="admin-stat-label">Packages sold</span>
        <strong><?= number_format((int) ($totals['packages_sold'] ?? 0)) ?></strong>
        <small>Units across paid orders</small>
    </article>
    <article class="admin-stat-card">
        <span class="admin-stat-icon icon-green"><i class="bi bi-currency-dollar"></i></span>
        <span class="admin-stat-label">Total sales</span>
        <strong>$<?= number_format(((int) ($totals['total_sales_cents'] ?? 0)) / 100, 2) ?></strong>
        <small><?= number_format((int) ($totals['paid_orders'] ?? 0)) ?> completed payments</small>
    </article>
</section>

<section class="admin-chart-grid" aria-label="Sales charts">
    <article class="admin-panel admin-chart-card">
        <div class="admin-panel-heading">
            <div>
                <h2 class="h5 fw-bold mb-1">Monthly sales</h2>
                <p class="text-secondary small mb-0">Paid revenue over the last 12 months</p>
            </div>
            <span class="admin-chart-total">$<?= number_format($monthSalesCents / 100, 2) ?><small>this month</small></span>
        </div>
        <div class="admin-chart-plot admin-month-chart" role="img" aria-label="Monthly paid sales over the last 12 months">
            <?php foreach ($monthlyChart as $month): ?>
                <?php $height = $month['sales_cents'] > 0 ? max(4, (int) round(($month['sales_cents'] / $monthlyMax) * 82)) : 0; ?>
                <div class="admin-chart-column" aria-label="<?= adminEscape($month['full_label']) ?>: $<?= number_format($month['sales_cents'] / 100, 2) ?>">
                    <span class="admin-month-chart-track">
                        <?php if ($month['sales_cents'] > 0): ?>
                            <span class="admin-chart-value">$<?= number_format($month['sales_cents'] / 100, 0) ?></span>
                        <?php endif; ?>
                        <span class="admin-chart-bar <?= $month['is_current'] ? 'current' : '' ?>" style="--bar-height: <?= $height ?>%"></span>
                    </span>
                    <span class="admin-chart-label"><?= adminEscape($month['label']) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </article>

    <article class="admin-panel admin-chart-card">
        <div class="admin-panel-heading">
            <div>
                <h2 class="h5 fw-bold mb-1">Daily sales</h2>
                <p class="text-secondary small mb-0">Paid revenue in <?= adminEscape($currentMonth->format('F')) ?> so far</p>
            </div>
            <span class="admin-chart-total">$<?= number_format($monthSalesCents / 100, 2) ?><small>month to date</small></span>
        </div>
        <div class="admin-chart-plot admin-daily-chart" role="img" aria-label="Daily paid sales for <?= adminEscape($currentMonth->format('F Y')) ?> through day <?= $currentDay ?>">
            <?php foreach ($dailyChart as $day): ?>
                <?php $height = $day['sales_cents'] > 0 ? max(3, (int) round(($day['sales_cents'] / $dailyMax) * 100)) : 0; ?>
                <div class="admin-chart-column" title="<?= adminEscape($currentMonth->format('M')) ?> <?= $day['day'] ?>: $<?= number_format($day['sales_cents'] / 100, 2) ?>">
                    <span class="admin-chart-bar <?= $day['day'] === $currentDay ? 'current' : '' ?>" style="--bar-height: <?= $height ?>%"></span>
                    <span class="admin-chart-label"><?= ($day['day'] === 1 || $day['day'] % 5 === 0 || $day['day'] === $currentDay) ? $day['day'] : '' ?></span>
                </div>
            <?php endforeach; ?>
        </div>
        <p class="admin-chart-footnote mb-0"><i class="bi bi-info-circle me-1"></i>Hover over a bar to see its date and exact sales amount.</p>
    </article>
</section>

<section class="admin-panel admin-recent-panel">
    <div class="admin-panel-heading">
        <div>
            <h2 class="h5 fw-bold mb-1">Recent orders</h2>
            <p class="text-secondary small mb-0">Latest customer activity and payment status.</p>
        </div>
        <a href="package-sales" class="btn btn-outline-primary btn-sm">View package sales <i class="bi bi-arrow-right ms-1"></i></a>
    </div>
    <div class="table-responsive">
        <table class="table admin-orders-table align-middle mb-0">
            <thead><tr><th>Order</th><th>Customer</th><th>Payment</th><th>Amount</th><th>Date</th></tr></thead>
            <tbody>
            <?php if ($latestOrders === []): ?>
                <tr><td class="text-center py-4 text-secondary" colspan="5">No orders yet. New package purchases will appear here.</td></tr>
            <?php else: foreach ($latestOrders as $order): ?>
                <tr>
                    <td><strong><?= adminEscape($order['order_number']) ?></strong></td>
                    <td><?= adminEscape($order['customer_first_name'] . ' ' . $order['customer_last_name']) ?></td>
                    <td><span class="admin-status admin-status-<?= adminEscape($order['payment_status']) ?>"><?= adminEscape(ucfirst(str_replace('_', ' ', $order['payment_status']))) ?></span></td>
                    <td class="fw-bold">$<?= number_format(((int) $order['total_cents']) / 100, 2) ?></td>
                    <td><?= adminEscape(date('M j, Y', strtotime($order['paid_at'] ?: $order['updated_at']))) ?></td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</section>
<?php require __DIR__ . '/_footer.php'; ?>
