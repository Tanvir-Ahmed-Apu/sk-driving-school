<?php

require_once __DIR__ . '/square.php';
require_once __DIR__ . '/permit-documents.php';

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
$checkoutError = null;

function checkoutPostString(string $key): string
{
    return isset($_POST[$key]) && is_string($_POST[$key]) ? trim($_POST[$key]) : '';
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
        || $date->format('Y-m-d') < date('Y-m-d')
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
    } elseif ($pdo === null) {
        http_response_code(503);
        $checkoutError = 'Checkout is temporarily unavailable because the order database is not connected.';
    } elseif (!isset($cart) || $cart === [] || $checkoutItems === []) {
        http_response_code(400);
        $checkoutError = 'Your cart is empty. Add a package before checkout.';
    } else {
        $existingOrderStatement = $pdo->prepare(
            'SELECT order_number, square_payment_url FROM orders WHERE checkout_token = ? LIMIT 1'
        );
        $existingOrderStatement->execute([$checkoutSubmissionToken]);
        $existingOrder = $existingOrderStatement->fetch();
        if (is_array($existingOrder) && is_string($existingOrder['square_payment_url']) && $existingOrder['square_payment_url'] !== '') {
            header('Location: ' . $existingOrder['square_payment_url'], true, 303);
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
        $courseDate = checkoutOptionalDate(checkoutPostString('course_date'));
        $timePreference = checkoutPostString('time');
        $allowedTimes = ['morning', 'afternoon', 'evening'];
        $instructor = checkoutPostString('instructor');
        $allowedInstructors = [
            'rajandrasinh' => 'RAJANDRASINH, B CHAVDA',
            'mohammed' => 'MOHAMMED, M ALAM',
        ];
        $addCourse = checkoutPostString('add_course') === '1';
        $requestCertificateDiscount = checkoutPostString('has_certificate') === '1';
        $certificateDiscountCents = $requestCertificateDiscount && !$addCourse ? 3000 : 0;

        $permitUploads = [];
        $permitUploadError = null;
        if (!is_array($existingOrder)) {
            try {
                $permitUploads = validatePermitImageUploads($_FILES);
            } catch (RuntimeException $e) {
                $permitUploadError = $e->getMessage();
            }
        }

        if (is_array($existingOrder)) {
            http_response_code(409);
            $checkoutError = 'Order ' . $existingOrder['order_number'] . ' was saved, but secure payment could not be started. Contact the school for help instead of submitting another order.';
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
            || ($addCourse && $courseDate === false)
            || !in_array($timePreference, $allowedTimes, true)
            || ($instructor !== '' && !isset($allowedInstructors[$instructor]))
            || checkoutPostString('terms') !== '1'
            || $permitUploadError !== null
        ) {
            http_response_code(422);
            $checkoutError = $permitUploadError ?? 'Please check your required contact details, address, and date selections.';
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
            if ($addCourse) {
                $subtotalCents += 5000;
                $orderItems[] = [
                    'name' => '5-Hour Pre-Licensing Course',
                    'category' => 'Course',
                    'region' => '',
                    'quantity' => 1,
                    'unit_price_cents' => 5000,
                    'line_total_cents' => 5000,
                ];
            }
            $totalCents = max(0, $subtotalCents - $certificateDiscountCents);
            if ($totalCents < 100) {
                http_response_code(422);
                $checkoutError = 'The order total is below the minimum Square checkout amount.';
            } else {
                $orderNumber = 'SK' . date('ymd') . strtoupper(bin2hex(random_bytes(3)));
                $publicToken = bin2hex(random_bytes(32));
                $storedPermitUploads = [];
                try {
                    $storedPermitUploads = storePermitImageUploads($permitUploads);
                } catch (RuntimeException $e) {
                    http_response_code(503);
                    $checkoutError = $e->getMessage();
                }

                if ($checkoutError === null) {
                    try {
                        $pdo->beginTransaction();
                        $insertOrder = $pdo->prepare(
                            'INSERT INTO orders (
                                order_number, public_token, checkout_token, customer_first_name, customer_last_name,
                                customer_email, customer_phone, address, city, postal_code,
                                preferred_date, preferred_time, instructor_preference, course_date,
                                subtotal_cents, discount_cents, total_cents, terms_accepted_at,
                                order_status, payment_status
                             ) VALUES (
                                :order_number, :public_token, :checkout_token, :first_name, :last_name,
                                :email, :phone, :address, :city, :postal_code,
                                :preferred_date, :preferred_time, :instructor, :course_date,
                                :subtotal, :discount, :total, CURRENT_TIMESTAMP, :order_status, :payment_status
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
                            'course_date' => $addCourse ? $courseDate : null,
                            'subtotal' => $subtotalCents,
                            'discount' => $certificateDiscountCents,
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
                        $insertDocument = $pdo->prepare(
                            'INSERT INTO order_documents (order_id, document_side, storage_key, mime_type) VALUES (?, ?, ?, ?)'
                        );
                        foreach ($storedPermitUploads as $side => $upload) {
                            $insertDocument->execute([
                                $orderId,
                                $side,
                                $upload['storage_key'],
                                $upload['mime_type'],
                            ]);
                        }
                        $pdo->commit();
                    } catch (PDOException $e) {
                        if ($pdo->inTransaction()) {
                            $pdo->rollBack();
                        }
                        deletePermitImageFiles(array_column($storedPermitUploads, 'storage_key'));
                        error_log('Could not save checkout order: ' . $e->getMessage());
                        http_response_code(503);
                        $checkoutError = 'We could not save your order. Please try again or contact the school.';
                    }
                }

                if ($checkoutError === null) {
                    try {
                        $locationId = appEnv('SQUARE_LOCATION_ID');
                        $appBaseUrl = rtrim(appEnv('APP_BASE_URL'), '/');
                        if ($locationId === '' || $appBaseUrl === '' || !in_array(parse_url($appBaseUrl, PHP_URL_SCHEME), ['http', 'https'], true)) {
                            throw new RuntimeException('Square location ID or public application URL is not configured.');
                        }

                        $client = squareClient();
                        $request = new Square\Checkout\PaymentLinks\Requests\CreatePaymentLinkRequest([
                            'idempotencyKey' => substr(hash('sha256', $checkoutSubmissionToken), 0, 32),
                            'description' => 'SK Driving School order ' . $orderNumber,
                            'quickPay' => new Square\Types\QuickPay([
                                'name' => 'SK Driving School order ' . $orderNumber,
                                'priceMoney' => new Square\Types\Money([
                                    'amount' => $totalCents,
                                    'currency' => 'USD',
                                ]),
                                'locationId' => $locationId,
                            ]),
                            'checkoutOptions' => new Square\Types\CheckoutOptions([
                                'redirectUrl' => $appBaseUrl . '/order-confirmation.php?ref=' . $publicToken,
                                'askForShippingAddress' => false,
                                'allowTipping' => false,
                            ]),
                            'paymentNote' => 'SK Driving School order ' . $orderNumber,
                        ]);
                        $response = $client->checkout->paymentLinks->create($request);
                        $paymentLink = $response->getPaymentLink();
                        if (
                            $paymentLink === null
                            || !is_string($paymentLink->getUrl())
                            || $paymentLink->getUrl() === ''
                            || !is_string($paymentLink->getOrderId())
                            || $paymentLink->getOrderId() === ''
                        ) {
                            throw new RuntimeException('Square did not return a usable payment link and order ID.');
                        }
                        $pdo->prepare(
                            "UPDATE orders
                             SET square_order_id = ?, square_payment_link_id = ?, square_payment_url = ?
                             WHERE id = ?"
                        )->execute([
                            $paymentLink->getOrderId(),
                            $paymentLink->getId(),
                            $paymentLink->getUrl(),
                            $orderId,
                        ]);
                        $_SESSION['cart'] = [];
                        $_SESSION['checkout_submission_token'] = bin2hex(random_bytes(32));
                        header('Location: ' . $paymentLink->getUrl(), true, 303);
                        exit;
                    } catch (PDOException $e) {
                        error_log('Could not save the Square payment link for ' . $orderNumber . ': ' . $e->getMessage());
                        http_response_code(503);
                        $checkoutError = 'Your order was saved, but we could not finish setting up payment. Contact the school with order ' . $orderNumber . ' for help.';
                    } catch (Square\Exceptions\SquareException | RuntimeException $e) {
                        error_log('Square checkout link creation failed for ' . $orderNumber . ': ' . $e->getMessage());
                        $pdo->prepare("UPDATE orders SET order_status = 'payment_link_error' WHERE id = ?")
                            ->execute([$orderId]);
                        http_response_code(503);
                        $checkoutError = 'Your order was saved, but we could not open secure payment. Contact the school with order ' . $orderNumber . ' so we can help.';
                    }
                }
            }
        }
    }
}
