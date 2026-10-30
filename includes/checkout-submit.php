<?php

require_once __DIR__ . '/square.php';
require_once __DIR__ . '/paid-order-email.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!isset($_SESSION['checkout_csrf']) || !is_string($_SESSION['checkout_csrf'])) {
    $_SESSION['checkout_csrf'] = bin2hex(random_bytes(32));
}
if (!isset($_SESSION['checkout_submission_token']) || !is_string($_SESSION['checkout_submission_token'])) {
    $_SESSION['checkout_submission_token'] = bin2hex(random_bytes(32));
}
$checkoutCsrfToken = $_SESSION['checkout_csrf'];
$checkoutSubmissionToken = $_SESSION['checkout_submission_token'];
$checkoutPaymentAttemptId = $_POST['payment_attempt_id'] ?? '';
if (!is_string($checkoutPaymentAttemptId) || preg_match('/\A[a-f0-9]{32}\z/', $checkoutPaymentAttemptId) !== 1) {
    $checkoutPaymentAttemptId = bin2hex(random_bytes(16));
}
$checkoutError = null;
$checkoutCardToken = $_POST['square_card_token'] ?? '';
$existingOrder = null;

function checkoutPostString(string $key): string
{
    return isset($_POST[$key]) && is_string($_POST[$key]) ? trim($_POST[$key]) : '';
}

function checkoutMinimumRoadTestDate(): string
{
    $schoolTimezone = new DateTimeZone('America/New_York');

    return (new DateTimeImmutable('tomorrow', $schoolTimezone))->format('Y-m-d');
}

function checkoutOptionalDate(string $value): string|false|null
{
    if ($value === '') {
        return null;
    }
    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
    $errors = DateTimeImmutable::getLastErrors();
    if (
        $date === false
        || ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0))
        || $date->format('Y-m-d') !== $value
        || $date->format('Y-m-d') < checkoutMinimumRoadTestDate()
    ) {
        return false;
    }

    return $value;
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $submittedToken = checkoutPostString('csrf_token');
    if (!hash_equals($checkoutCsrfToken, $submittedToken)) {
        http_response_code(400);
        $checkoutError = 'Your session expired. Refresh the page and submit your order again.';
    } elseif (!hash_equals($checkoutSubmissionToken, checkoutPostString('checkout_token'))) {
        http_response_code(409);
        $checkoutError = 'This checkout form has already been submitted. Refresh the page to begin a new order.';
    } elseif (!is_string($checkoutCardToken) || $checkoutCardToken === '' || strlen($checkoutCardToken) > 2048) {
        http_response_code(422);
        $checkoutError = 'Enter your card details and wait for Square to verify them before placing your order.';
    } elseif ($pdo === null) {
        http_response_code(503);
        $checkoutError = 'Checkout is temporarily unavailable because the order database is not connected.';
    } elseif (!isset($cart) || $cart === [] || $checkoutItems === []) {
        http_response_code(400);
        $checkoutError = 'Your cart is empty. Add a package before checkout.';
    } else {
        $existingOrderStatement = $pdo->prepare(
            'SELECT id, order_number, public_token, customer_email, total_cents,
                    payment_status, square_payment_id, square_payment_url
             FROM orders WHERE checkout_token = ? LIMIT 1'
        );
        $existingOrderStatement->execute([$checkoutSubmissionToken]);
        $existingOrder = $existingOrderStatement->fetch();
        if (is_array($existingOrder) && is_string($existingOrder['square_payment_url']) && $existingOrder['square_payment_url'] !== '') {
            header('Location: ' . $existingOrder['square_payment_url'], true, 303);
            exit;
        }
        if (
            is_array($existingOrder)
            && (
                $existingOrder['payment_status'] === 'paid'
                || (
                    $existingOrder['payment_status'] === 'pending'
                    && $existingOrder['square_payment_id'] !== null
                )
            )
        ) {
            header('Location: order-confirmation.php?ref=' . rawurlencode($existingOrder['public_token']), true, 303);
            exit;
        }
        $customer = [
            'first_name' => checkoutPostString('first_name'),
            'last_name' => checkoutPostString('last_name'),
            'email' => checkoutPostString('email'),
            'phone' => checkoutPostString('phone'),
            'address' => checkoutPostString('address'),
            'city' => checkoutPostString('city'),
            'postal_code' => checkoutPostString('postal_code'),
        ];
        $roadTestDate = checkoutOptionalDate(checkoutPostString('road_test_date'));
        $timePreference = checkoutPostString('time');
        $allowedTimes = ['11:00 AM'];
        $instructor = checkoutPostString('instructor');
        $allowedInstructors = [
            'rajandrasinh' => 'RAJANDRASINH, B CHAVDA',
            'mohammed' => 'MOHAMMED, M ALAM',
        ];
        if (is_array($existingOrder)) {
            http_response_code(409);
            $checkoutError = 'Order ' . $existingOrder['order_number'] . ' was already saved, but its payment status is not confirmed.';
        } elseif (
            in_array('', $customer, true)
            || !filter_var($customer['email'], FILTER_VALIDATE_EMAIL)
            || strlen($customer['email']) > 254
            || strlen($customer['first_name']) > 100
            || strlen($customer['last_name']) > 100
            || strlen($customer['phone']) > 40
            || strlen($customer['address']) > 255
            || strlen($customer['city']) > 120
            || strlen($customer['postal_code']) > 20
            || $roadTestDate === false
            || !in_array($timePreference, $allowedTimes, true)
            || ($instructor !== '' && !isset($allowedInstructors[$instructor]))
            || checkoutPostString('terms') !== '1'
        ) {
            http_response_code(422);
            $checkoutError = 'Please check your required contact details, address, and date selections.';
        } else {
            $subtotalCents = 0;
            $orderItems = [];
            foreach ($checkoutItems as $item) {
                $unitPriceCents = (int) round(((float) $item['price']) * 100);
                $lineTotalCents = $unitPriceCents * (int) $item['quantity'];
                $subtotalCents += $lineTotalCents;
                $orderItems[] = [
                    'name' => $item['name'],
                    'category' => $item['category'],
                    'region' => $item['region'],
                    'quantity' => (int) $item['quantity'],
                    'unit_price_cents' => $unitPriceCents,
                    'line_total_cents' => $lineTotalCents,
                ];
            }
            $totalCents = $subtotalCents;
            if ($totalCents < 100) {
                http_response_code(422);
                $checkoutError = 'The order total is below the minimum Square checkout amount.';
            } elseif (
                appEnv('SQUARE_APPLICATION_ID') === ''
                || appEnv('SQUARE_ACCESS_TOKEN') === ''
                || appEnv('SQUARE_LOCATION_ID') === ''
                || !in_array(appEnv('SQUARE_ENVIRONMENT', 'sandbox'), ['sandbox', 'production'], true)
            ) {
                http_response_code(503);
                $checkoutError = 'Card payments are temporarily unavailable. Contact the school for help.';
            } else {
                try {
                    $pdo->beginTransaction();
                    $orderNumber = 'SK' . date('ymd') . strtoupper(bin2hex(random_bytes(3)));
                    $publicToken = bin2hex(random_bytes(32));
                    $insertOrder = $pdo->prepare(
                        'INSERT INTO orders (
                            order_number, public_token, checkout_token, customer_first_name, customer_last_name,
                            customer_email, customer_phone, address, city, postal_code,
                            preferred_date, preferred_time, instructor_preference,
                            subtotal_cents, total_cents, terms_accepted_at,
                            order_status, payment_status
                         ) VALUES (
                             :order_number, :public_token, :checkout_token, :first_name, :last_name,
                             :email, :phone, :address, :city, :postal_code,
                             :preferred_date, :preferred_time, :instructor,
                             :subtotal, :total, CURRENT_TIMESTAMP, :order_status, :payment_status
                        )'
                    );
                    $insertOrder->execute([
                        'order_number' => $orderNumber,
                        'public_token' => $publicToken,
                        'checkout_token' => $checkoutSubmissionToken,
                        'first_name' => $customer['first_name'],
                        'last_name' => $customer['last_name'],
                        'email' => $customer['email'],
                        'phone' => $customer['phone'],
                        'address' => $customer['address'],
                        'city' => $customer['city'],
                        'postal_code' => $customer['postal_code'],
                        'preferred_date' => $roadTestDate,
                        'preferred_time' => $timePreference,
                        'instructor' => $allowedInstructors[$instructor] ?? null,
                        'subtotal' => $subtotalCents,
                        'total' => $totalCents,
                        'order_status' => 'awaiting_payment',
                        'payment_status' => 'pending',
                    ]);
                    $orderId = (int) $pdo->lastInsertId();
                    $insertItem = $pdo->prepare(
                        'INSERT INTO order_items (
                            order_id, item_name, category, region, quantity, unit_price_cents, line_total_cents
                         ) VALUES (?, ?, ?, ?, ?, ?, ?)'
                    );
                    foreach ($orderItems as $item) {
                        $insertItem->execute([
                            $orderId,
                            $item['name'],
                            $item['category'],
                            $item['region'],
                            $item['quantity'],
                            $item['unit_price_cents'],
                            $item['line_total_cents'],
                        ]);
                    }
                    $pdo->commit();
                } catch (PDOException $e) {
                    if ($pdo->inTransaction()) {
                        $pdo->rollBack();
                    }
                    error_log('Could not save checkout order: ' . $e->getMessage());
                    http_response_code(503);
                    $checkoutError = 'We could not save your order. Please try again or contact the school.';
                }

                if ($checkoutError === null) {
                    try {
                        $service = squarePaymentService();
                        $paymentResponse = $service->createPayment(
                            totalCents: $totalCents,
                            cardToken: $checkoutCardToken,
                            orderNumber: $orderNumber,
                            customerEmail: $customer['email']
                        );
                        $payment = $paymentResponse->getPayment();

                        if ($payment === null || !is_string($payment->getId()) || $payment->getId() === '') {
                            $pdo->prepare(
                                "UPDATE orders SET payment_status = 'failed' WHERE id = ? AND payment_status = 'pending'"
                            )->execute([$orderId]);
                            $_SESSION['checkout_submission_token'] = bin2hex(random_bytes(32));
                            $checkoutSubmissionToken = $_SESSION['checkout_submission_token'];
                            $checkoutPaymentAttemptId = bin2hex(random_bytes(16));
                            http_response_code(402);
                            $checkoutError = 'Square could not process this card. Check the details or try another card.';
                        } elseif (in_array($payment->getStatus(), ['FAILED', 'CANCELED'], true)) {
                            $pdo->prepare(
                                "UPDATE orders SET payment_status = 'failed' WHERE id = ? AND payment_status = 'pending'"
                            )->execute([$orderId]);
                            $_SESSION['checkout_submission_token'] = bin2hex(random_bytes(32));
                            $checkoutSubmissionToken = $_SESSION['checkout_submission_token'];
                            $checkoutPaymentAttemptId = bin2hex(random_bytes(16));
                            http_response_code(402);
                            $checkoutError = 'Your card was not charged. Check the details or try another card.';
                        } elseif (!in_array($payment->getStatus(), ['COMPLETED', 'APPROVED'], true)) {
                            throw new RuntimeException('Square returned an unexpected card payment status.');
                        } else {
                            $paymentId = $payment->getId();
                            $paymentCompleted = $payment->getStatus() === 'COMPLETED';
                            $finalPaymentStatus = $paymentCompleted ? 'paid' : 'pending';

                            $updatePayment = $pdo->prepare(
                                "UPDATE orders
                                 SET square_payment_id = ?,
                                     payment_status = ?,
                                     paid_at = IF(? = 'paid', COALESCE(paid_at, CURRENT_TIMESTAMP), paid_at),
                                     order_status = IF(? = 'paid' AND order_status IN ('awaiting_payment', 'payment_link_error'), 'new', order_status)
                                 WHERE id = ? AND payment_status <> 'paid'"
                            );
                            $updatePayment->execute([
                                $paymentId,
                                $finalPaymentStatus,
                                $finalPaymentStatus,
                                $finalPaymentStatus,
                                $orderId,
                            ]);
                            if ($updatePayment->rowCount() === 0) {
                                $savedPaymentStatement = $pdo->prepare(
                                    'SELECT payment_status, square_payment_id FROM orders WHERE id = ?'
                                );
                                $savedPaymentStatement->execute([$orderId]);
                                $savedPayment = $savedPaymentStatement->fetch();
                                if (
                                    !is_array($savedPayment)
                                    || $savedPayment['payment_status'] !== $finalPaymentStatus
                                    || $savedPayment['square_payment_id'] !== $paymentId
                                ) {
                                    throw new RuntimeException('Square accepted the card payment, but its status could not be saved.');
                                }
                            }

                            if ($paymentCompleted) {
                                try {
                                    sendPaidOrderReceipt($pdo, $orderId);
                                } catch (Throwable $exception) {
                                    error_log('Could not send payment receipt for order ' . $orderNumber . ': ' . $exception->getMessage());
                                }
                            }

                            $_SESSION['cart'] = [];
                            $_SESSION['checkout_submission_token'] = bin2hex(random_bytes(32));
                            header('Location: order-confirmation.php?ref=' . rawurlencode($publicToken), true, 303);
                            exit;
                        }
                    } catch (PDOException $e) {
                        error_log('Could not save Square card payment for ' . $orderNumber . ': ' . $e->getMessage());
                        http_response_code(503);
                        $checkoutError = 'We could not confirm the payment status. Contact the school with order ' . $orderNumber . ' before trying again.';
                    } catch (Square\Exceptions\SquareException | RuntimeException $e) {
                        error_log('Square card payment failed for ' . $orderNumber . ': ' . $e->getMessage());
                        http_response_code(503);
                        $checkoutError = 'We could not confirm the card payment. Contact the school with order ' . $orderNumber . ' before trying again.';
                    }
                }
            }
        }
    }
}