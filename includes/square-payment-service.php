<?php

/**
 * Square Payment Service
 *
 * Encapsulates all Square API interactions for payment processing,
 * webhook verification, and order status management.
 *
 * @see https://square.github.io/square-php-sdk/
 */
declare(strict_types=1);

namespace Square\Services;

use Square\SquareClient;
use Square\Exceptions\SquareApiException;
use Square\Exceptions\SquareException;
use Square\Payments\Requests\CreatePaymentRequest;
use Square\Types\CashPaymentDetails;
use Square\Types\Currency;
use Square\Types\CreatePaymentResponse;
use Square\Types\Money;
use Square\Utils\WebhooksHelper;
use Square\Reporting\Requests\LoadRequest;
use Square\Types\Query;
use Square\Utils\ReportingHelper;
use PDO;
use RuntimeException;

/**
 * Service class for Square payment operations.
 */
class PaymentService
{
    /** @var Square\SquareClient */
    protected $client;

    /** @var string */
    protected $environment;

    /** @var string */
    protected $accessToken;

    /** @var string */
    protected $applicationId;

    /** @var string */
    protected $locationId;

    /**
     * PaymentService constructor.
     *
     * Initializes the Square client from environment variables.
     * Throws RuntimeException if Square is not configured.
     */
    public function __construct()
    {
        $this->environment = appEnv('SQUARE_ENVIRONMENT', 'sandbox');
        $this->accessToken = appEnv('SQUARE_ACCESS_TOKEN');
        $this->applicationId = appEnv('SQUARE_APPLICATION_ID');
        $this->locationId = appEnv('SQUARE_LOCATION_ID');

        if ($this->accessToken === '') {
            throw new RuntimeException('Square payments are not configured.');
        }

        $this->client = new SquareClient(
            token: $this->accessToken,
            options: ['baseUrl' => $this->getBaseUrl()]
        );
    }

    /**
     * Get the base URL for the Square environment.
     */
    protected function getBaseUrl(): string
    {
        return match($this->environment) {
            'production' => \Square\Environments::Production->value,
            'sandbox' => \Square\Environments::Sandbox->value,
            default => \Square\Environments::Sandbox->value,
        };
    }

    /**
     * Create a payment for the given order.
     *
     * @param int $totalCents Order total in cents
     * @param string $cardToken Square card nonce/token
     * @param string $orderNumber Merchant order number
     * @param string $customerEmail Customer email address
     *
     * @return \Square\Payments\Response\CreatePaymentResponse
     *
     * @throws SquareException|SquareApiException on payment failure
     */
    public function createPayment(int $totalCents, string $cardToken, string $orderNumber, string $customerEmail): CreatePaymentResponse
    {
        $idempotencyKey = bin2hex(random_bytes(16));
        $request = new CreatePaymentRequest([
            'sourceId' => $cardToken,
            'idempotencyKey' => $idempotencyKey,
            'autocomplete' => true,
            'amountMoney' => new Money([
                'amount' => $totalCents,
                'currency' => Currency::Usd->value,
            ]),
            'locationId' => $this->locationId,
            'referenceId' => $orderNumber,
            'buyerEmailAddress' => $customerEmail,
            'note' => 'SK Driving School order ' . $orderNumber,
        ]);

        return $this->client->payments->create($request);
    }

    /**
     * Verify a Square webhook signature.
     *
     * @param string $body       Raw request body
     * @param string $signature  HMAC-SHA256 signature header value
     * @param string $key        Webhook signature key
     * @param string $notificationUrl URL where events are sent
     *
     * @return bool True if signature is valid, false otherwise
     */
    public static function verifyWebhookSignature(string $body, string $signature, string $key, string $notificationUrl): bool
    {
        if ($signature === '' || $key === '' || $notificationUrl === '') {
            return false;
        }

        return WebhooksHelper::verifySignature($body, $signature, $key, $notificationUrl);
    }

    /**
     * Load a Reporting API query and wait for results.
     *
     * @param Query $query The reporting query to execute
     *
     * @return mixed The resolved query result
     *
     * @throws SquareException if the query does not resolve within maxAttempts
     */
    public function loadAndWait(Query $query): array
    {
        $request = new LoadRequest([
            'query' => $query,
        ]);

        return ReportingHelper::loadAndWait(
            $this,
            $request,
            [
                'maxAttempts' => 20,
                'initialDelayMs' => 2000,
                'maxDelayMs' => 20000,
                'backoffFactor' => 2,
            ]
        );
    }

    /**
     * Get the Square client instance.
     *
     * @return Square\SquareClient
     */
    public function getClient(): SquareClient
    {
        return $this->client;
    }

    /**
     * Get the current environment.
     *
     * @return string
     */
    public function getEnvironment(): string
    {
        return $this->environment;
    }

    /**
     * Get the application ID.
     *
     * @return string
     */
    public function getApplicationId(): string
    {
        return $this->applicationId;
    }

    /**
     * Get the location ID.
     *
     * @return string
     */
    public function getLocationId(): string
    {
        return $this->locationId;
    }
}