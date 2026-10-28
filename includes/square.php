<?php

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/square-payment-service.php';

/**
 * Factory function to create a PaymentService instance.
 *
 * @return \Square\Services\PaymentService
 *
 * @throws \RuntimeException If Square payments are not configured.
 */
function squarePaymentService(): \Square\Services\PaymentService
{
    return new \Square\Services\PaymentService();
}

/**
 * Get the Square environment.
 *
 * @return \Square\Environments
 */
function squareEnvironment(): \Square\Environments
{
    $environment = appEnv('SQUARE_ENVIRONMENT', 'sandbox');
    if ($environment === 'production') {
        return \Square\Environments::Production;
    }
    if ($environment === 'sandbox') {
        return \Square\Environments::Sandbox;
    }

    throw new RuntimeException('SQUARE_ENVIRONMENT must be sandbox or production.');
}