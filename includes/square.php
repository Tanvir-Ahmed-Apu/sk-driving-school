<?php

require_once __DIR__ . '/db.php';

function squareEnvironment(): Square\Environments
{
    $environment = appEnv('SQUARE_ENVIRONMENT', 'sandbox');
    if ($environment === 'production') {
        return Square\Environments::Production;
    }
    if ($environment === 'sandbox') {
        return Square\Environments::Sandbox;
    }

    throw new RuntimeException('SQUARE_ENVIRONMENT must be sandbox or production.');
}

function squareClient(): Square\SquareClient
{
    $accessToken = appEnv('SQUARE_ACCESS_TOKEN');
    if ($accessToken === '') {
        throw new RuntimeException('Square payments are not configured.');
    }

    return new Square\SquareClient(
        token: $accessToken,
        options: ['baseUrl' => squareEnvironment()->value]
    );
}
