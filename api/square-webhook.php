<?php

require_once __DIR__ . '/../includes/square.php';
require_once __DIR__ . '/../includes/paid-order-email.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit;
}

$signatureKey = appEnv('SQUARE_WEBHOOK_SIGNATURE_KEY');
$webhookUrl = appEnv('SQUARE_WEBHOOK_URL');
$signature = $_SERVER['HTTP_X_SQUARE_HMACSHA256_SIGNATURE'] ?? '';
$body = file_get_contents('php://input');

// Use the Square\Services\PaymentService for webhook verification
// Create a minimal instance to access the static verify method
// We can verify directly using WebhooksHelper since it's static

if (
    $pdo === null
    || $signatureKey === ''
    || $webhookUrl === ''
    || !is_string($signature)
    || !is_string($body)
    || !\Square\Utils\WebhooksHelper::verifySignature($body, $signature, $signatureKey, $webhookUrl)
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
if (
    !is_array($payment)
    || !isset($payment['id'], $payment['status'])
    || !is_string($payment['id'])
    || !is_string($payment['status'])
) {
    http_response_code(400);
    exit;
}

if ($payment['status'] === 'COMPLETED') {
    $squareOrderId = is_string($payment['order_id'] ?? null) && $payment['order_id'] !== ''
        ? $payment['order_id']
        : null;
    $orderNumber = is_string($payment['reference_id'] ?? null) && $payment['reference_id'] !== ''
        ? $payment['reference_id']
        : null;
    if ($squareOrderId === null && $orderNumber === null) {
        http_response_code(400);
        exit;
    }

    $amount = $payment['amount_money']['amount'] ?? null;
    $currency = $payment['amount_money']['currency'] ?? null;
    if (!is_int($amount) || $currency !== 'USD') {
        error_log('Rejected Square payment event with missing amount or unexpected currency.');
        http_response_code(400);
        exit;
    }

    $findOrder = $squareOrderId !== null
        ? $pdo->prepare(
            "SELECT id, payment_status, square_payment_id
             FROM orders
             WHERE square_order_id = ? AND total_cents = ? AND currency = 'USD'
             LIMIT 1"
        )
        : $pdo->prepare(
            "SELECT id, payment_status, square_payment_id
             FROM orders
             WHERE order_number = ? AND total_cents = ? AND currency = 'USD'
             LIMIT 1"
        );
    $findOrder->execute($squareOrderId !== null ? [$squareOrderId, $amount] : [$orderNumber, $amount]);
    $matchedOrder = $findOrder->fetch();
    if (!is_array($matchedOrder)) {
        error_log('Square payment event did not match a pending order amount and reference.');
    } elseif (
        $matchedOrder['payment_status'] === 'paid'
        && $matchedOrder['square_payment_id'] !== $payment['id']
    ) {
        error_log('Square payment event did not match the already-recorded payment for its order.');
    } else {
        $paymentConfirmed = $matchedOrder['payment_status'] === 'paid';
        if (!$paymentConfirmed) {
            $statement = $pdo->prepare(
                "UPDATE orders
                 SET payment_status = 'paid',
                     paid_at = COALESCE(paid_at, CURRENT_TIMESTAMP),
                     order_status = IF(order_status IN ('awaiting_payment', 'payment_link_error'), 'new', order_status),
                     square_payment_id = ?
                 WHERE id = ? AND total_cents = ? AND currency = 'USD' AND payment_status <> 'paid'
                   AND (square_payment_id IS NULL OR square_payment_id = ?)"
            );
            $statement->execute([$payment['id'], (int) $matchedOrder['id'], $amount, $payment['id']]);
            $paymentConfirmed = $statement->rowCount() > 0;
            if (!$paymentConfirmed) {
                $checkOrder = $pdo->prepare(
                    'SELECT payment_status, square_payment_id FROM orders WHERE id = ?'
                );
                $checkOrder->execute([(int) $matchedOrder['id']]);
                $currentOrder = $checkOrder->fetch();
                $paymentConfirmed = is_array($currentOrder)
                    && $currentOrder['payment_status'] === 'paid'
                    && $currentOrder['square_payment_id'] === $payment['id'];
            }
        }

        if (!$paymentConfirmed) {
            error_log('Square payment could not be applied to its matching order.');
            http_response_code(500);
            exit;
        }

        try {
            sendPaidOrderReceipt($pdo, (int) $matchedOrder['id']);
        } catch (Throwable $exception) {
            error_log('Could not send payment receipt for Square order: ' . $exception->getMessage());
            http_response_code(500);
            exit;
        }
    }
}

http_response_code(200);
header('Content-Type: text/plain; charset=utf-8');
echo 'OK';