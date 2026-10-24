<?php

require_once __DIR__ . '/../includes/square.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit;
}

$signatureKey = appEnv('SQUARE_WEBHOOK_SIGNATURE_KEY');
$webhookUrl = appEnv('SQUARE_WEBHOOK_URL');
$signature = $_SERVER['HTTP_X_SQUARE_HMACSHA256_SIGNATURE'] ?? '';
$body = file_get_contents('php://input');
if (
    $pdo === null
    || $signatureKey === ''
    || $webhookUrl === ''
    || !is_string($signature)
    || !is_string($body)
    || !Square\Utils\WebhooksHelper::verifySignature($body, $signature, $signatureKey, $webhookUrl)
) {
    error_log('Rejected Square webhook: configuration, database, or signature verification failed.');
    http_response_code(403);
    exit;
}

$event = json_decode($body, true);
if (!is_array($event) || ($event['type'] ?? '') !== 'payment.updated') {
    http_response_code(200);
    exit;
}

$payment = $event['data']['object']['payment'] ?? null;
if (!is_array($payment) || !isset($payment['order_id'], $payment['id'], $payment['status'])) {
    http_response_code(400);
    exit;
}

if ($payment['status'] === 'COMPLETED') {
    $amount = $payment['amount_money']['amount'] ?? null;
    $currency = $payment['amount_money']['currency'] ?? null;
    if (!is_int($amount) || $currency !== 'USD') {
        error_log('Rejected Square payment event with missing amount or unexpected currency.');
        http_response_code(400);
        exit;
    }

    $statement = $pdo->prepare(
        "UPDATE orders
         SET payment_status = 'paid',
             paid_at = COALESCE(paid_at, CURRENT_TIMESTAMP),
             order_status = IF(order_status IN ('awaiting_payment', 'payment_link_error'), 'new', order_status),
             square_payment_id = ?
         WHERE square_order_id = ? AND total_cents = ? AND currency = 'USD' AND payment_status <> 'paid'"
    );
    $statement->execute([$payment['id'], $payment['order_id'], $amount]);
    if ($statement->rowCount() > 0) {
        $orderStatement = $pdo->prepare('SELECT id FROM orders WHERE square_order_id = ?');
        $orderStatement->execute([$payment['order_id']]);
        $matchedOrder = $orderStatement->fetch();
        if (is_array($matchedOrder)) {
            $pdo->prepare(
                "INSERT INTO order_events (order_id, event_type, details) VALUES (?, 'payment_confirmed', ?)"
            )->execute([(int) $matchedOrder['id'], 'Square payment ' . $payment['id']]);
        }
    } else {
        $check = $pdo->prepare('SELECT payment_status FROM orders WHERE square_order_id = ? AND total_cents = ?');
        $check->execute([$payment['order_id'], $amount]);
        $existing = $check->fetch();
        if (!is_array($existing) || $existing['payment_status'] !== 'paid') {
            error_log('Square payment event did not match a pending order amount and ID.');
        }
    }
}

http_response_code(200);
header('Content-Type: text/plain; charset=utf-8');
echo 'OK';
