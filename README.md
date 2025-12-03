# /!\ TODO: This README is outdated

# PHP Client for the Linxo API

[![Build Status](https://github.com/assoconnect/linxo-client/actions/workflows/build.yml/badge.svg)](https://github.com/assoconnect/linxo-client/actions/workflows/build.yml)
[![Quality Gate Status](https://sonarcloud.io/api/project_badges/measure?project=assoconnect_linxo-client&metric=alert_status)](https://sonarcloud.io/dashboard?id=assoconnect_linxo-client)

## Installation

```
composer require assoconnect/linxo-client
```

## Documentation

Use the official [documentation](https://developers.linxo.com/reference-accounts-api).

### Quick start
```php
<?php

use AssoConnect\LinxoClient\AuthClient;

// Set up the AuthClient with your credentials
$authClient = new AuthClient(
    'clientId',
    'clientSecret',
    'http://your-app.com/linxo/redirect'
);

// OAuth2 code exchange
$token = $authClient->getTokenFromCode('code');

// OAuth2 token refresh
$newToken = $authClient->refreshToken($token->getRefreshToken());

// Get an API Client for a given user identified by its access token
$apiClient = $authClient->createApiClient($token->getToken());
$apiClient->getAccounts(); // List of Linxo bank accounts
```

## Tests
This lib ships with a Guzzle system to mock API responses.

It will answer a mocked response if one is defined for a given request, or will call the API.

### How to use it
```php
<?php

use AssoConnect\LinxoClient\Dto\AccountDto;
use AssoConnect\LinxoClient\Test\MockAuthClient;
use AssoConnect\LinxoClient\Test\MockFactory;

$authClient = new MockAuthClient(
    'clientId',
    'clientSecret',
    'http://your-app.com/linxo/redirect'
);
$middleware = $authClient->getMiddleware();
// Create your own mock responses
$middleware->stackAccount([
    'id' => '1',
    'connection_id' => '2',
    'name' => 'My account',
    'iban' => 'FR4930003000703896912638U72',
    'status' => AccountDto::STATUS_ACTIVE,
    'currency' => 'EUR',
]);
// You can also mock you or transactions
$middleware->stackMe(...);
$middleware->stackTransaction(...);

$apiClient = $authClient->createApiClient('token');
$apiClient->getAccount('1'); // Will return the mocked DTO
$apiClient->getAccount('2'); // Will call the API

// Or you can use a factory to get predefined content
$factory = new MockFactory($middleware);
$factory->mockAccount([
    'name' => 'My 2nd account' // Replace only what you want
]);
$factory->mockMe(...);
$factory->mockTransaction(...);
```
