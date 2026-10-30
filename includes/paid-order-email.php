<?php

use PHPMailer\PHPMailer\PHPMailer;

require_once __DIR__ . '/db.php';

function sendPaidOrderReceipt(PDO $pdo, int $orderId): void
{
    $pdo->beginTransaction();

    try {
        $orderStatement = $pdo->prepare(
            'SELECT order_number, customer_first_name, customer_email, total_cents, currency, paid_at,
                    payment_status, receipt_email_sent_at
             FROM orders
             WHERE id = ?
             FOR UPDATE'
        );
        $orderStatement->execute([$orderId]);
        $order = $orderStatement->fetch();

        if (
            !is_array($order)
            || $order['payment_status'] !== 'paid'
            || $order['receipt_email_sent_at'] !== null
        ) {
            $pdo->commit();
            return;
        }

        $smtpHost = appEnv('SMTP_HOST');
        $smtpPortValue = appEnv('SMTP_PORT', '587');
        $smtpUsername = appEnv('SMTP_USERNAME');
        $smtpPassword = appEnv('SMTP_PASSWORD');
        $smtpEncryption = strtolower(appEnv('SMTP_ENCRYPTION', 'tls'));
        $fromEmail = appEnv('SMTP_FROM_EMAIL');
        $fromName = appEnv('SMTP_FROM_NAME', 'SK Driving School');
        $smtpPort = ctype_digit($smtpPortValue) ? (int) $smtpPortValue : 0;

        if (
            $smtpHost === ''
            || $smtpPort < 1
            || $smtpPort > 65535
            || !filter_var($fromEmail, FILTER_VALIDATE_EMAIL)
            || !filter_var($order['customer_email'], FILTER_VALIDATE_EMAIL)
            || !in_array($smtpEncryption, ['tls', 'ssl', 'none'], true)
            || (($smtpUsername === '') !== ($smtpPassword === ''))
        ) {
            throw new RuntimeException('SMTP configuration or customer email is invalid.');
        }

        $itemsStatement = $pdo->prepare(
            'SELECT item_name, category, region, quantity, unit_price_cents, line_total_cents
             FROM order_items
             WHERE order_id = ?
             ORDER BY id'
        );
        $itemsStatement->execute([$orderId]);
        $items = $itemsStatement->fetchAll();

        if ($items === []) {
            throw new RuntimeException('Paid order has no order items for its receipt.');
        }

        $escape = static fn(string $value): string => htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $itemRows = '';
        $plainItems = [];
        foreach ($items as $item) {
            $name = (string) $item['item_name'];
            $region = is_string($item['region']) && $item['region'] !== ''
                ? ' — ' . $item['region']
                : '';
            $quantity = (int) $item['quantity'];
            $unitPrice = number_format(((int) $item['unit_price_cents']) / 100, 2);
            $lineTotal = number_format(((int) $item['line_total_cents']) / 100, 2);
            $itemRows .= '<tr><td style="padding:8px;border-bottom:1px solid #e5ebf3;">'
                . $escape($name . $region) . '<br><small>' . $escape((string) $item['category'])
                . ' · ' . $quantity . ' × $' . $unitPrice . '</small></td><td style="padding:8px;border-bottom:1px solid #e5ebf3;text-align:right;">$'
                . $lineTotal . '</td></tr>';
            $plainItems[] = $name . $region . ' — ' . $quantity . ' × $' . $unitPrice . ' = $' . $lineTotal;
        }

        $orderNumber = (string) $order['order_number'];
        $total = number_format(((int) $order['total_cents']) / 100, 2);
        $firstName = (string) $order['customer_first_name'];
        $mailer = new PHPMailer(true);
        $mailer->isSMTP();
        $mailer->Host = $smtpHost;
        $mailer->Port = $smtpPort;
        $mailer->Timeout = 15;
        $mailer->SMTPAuth = $smtpUsername !== '';
        if ($mailer->SMTPAuth) {
            $mailer->Username = $smtpUsername;
            $mailer->Password = $smtpPassword;
        }
        $mailer->SMTPSecure = match ($smtpEncryption) {
            'tls' => PHPMailer::ENCRYPTION_STARTTLS,
            'ssl' => PHPMailer::ENCRYPTION_SMTPS,
            default => '',
        };
        $mailer->SMTPAutoTLS = $smtpEncryption !== 'none';
        $mailer->CharSet = PHPMailer::CHARSET_UTF8;
        $mailer->setFrom($fromEmail, $fromName);
        $mailer->addAddress((string) $order['customer_email'], $firstName);
        $mailer->isHTML(true);
        $mailer->Subject = 'Payment confirmation for order ' . $orderNumber;
        $mailer->Body = '<div style="font-family:Arial,sans-serif;color:#172335;line-height:1.6;max-width:640px;margin:auto;">'
            . '<h1 style="font-size:24px;">Thank you for your payment, ' . $escape($firstName) . '!</h1>'
            . '<p>We have confirmed your payment for order <strong>' . $escape($orderNumber) . '</strong>.</p>'
            . '<table style="width:100%;border-collapse:collapse;"><thead><tr><th style="text-align:left;padding:8px;border-bottom:2px solid #e5ebf3;">Package</th>'
            . '<th style="text-align:right;padding:8px;border-bottom:2px solid #e5ebf3;">Amount</th></tr></thead><tbody>'
            . $itemRows
            . '</tbody><tfoot><tr><th style="text-align:left;padding:10px 8px;">Total paid</th><th style="text-align:right;padding:10px 8px;">$'
            . $total . ' ' . $escape((string) $order['currency']) . '</th></tr></tfoot></table>'
            . '<p>Your order is awaiting our team’s review. We’ll follow up to confirm your schedule.</p>'
            . '<p>SK Driving School</p></div>';
        $mailer->AltBody = "Thank you for your payment, {$firstName}!\n"
            . "Payment confirmed for order {$orderNumber}.\n\n"
            . implode("\n", $plainItems) . "\n\n"
            . 'Total paid: $' . $total . ' ' . $order['currency'] . "\n"
            . "Your order is awaiting our team’s review. We’ll follow up to confirm your schedule.\n"
            . 'SK Driving School';
        $mailer->send();

        $sentStatement = $pdo->prepare(
            'UPDATE orders SET receipt_email_sent_at = CURRENT_TIMESTAMP
             WHERE id = ? AND payment_status = \'paid\' AND receipt_email_sent_at IS NULL'
        );
        $sentStatement->execute([$orderId]);
        if ($sentStatement->rowCount() !== 1) {
            throw new RuntimeException('Could not record the sent payment receipt.');
        }

        $pdo->commit();
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $exception;
    }
}
