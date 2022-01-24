<?php

declare(strict_types=1);

namespace AssoConnect\LinxoClient\Tests\Test;

use AssoConnect\LinxoClient\ApiClient;
use AssoConnect\LinxoClient\Test\MockAuthClient;
use AssoConnect\LinxoClient\Test\MockFactory;
use GuzzleHttp\Exception\ClientException;
use PHPUnit\Framework\TestCase;

class MockMiddlewareTest extends TestCase
{
    private ApiClient $apiClient;
    private MockFactory $factory;

    public function setUp(): void
    {
        $authClient = new MockAuthClient(
            'client id',
            'client secret',
            'http://linxo.com'
        );

        $this->apiClient = $authClient->createApiClient(MockAuthClient::ACCESS_TOKEN);
        $middleware = $authClient->getMiddleware();
        $this->factory = new MockFactory($middleware);
    }

    public function testGetAccountIsOverridden(): void
    {
        $this->factory->mockAccount();
        $account = $this->apiClient->getAccount('1');

        self::assertSame('1', $account->getId());
    }

    public function testGetAccountIsNotOverridden(): void
    {
        $this->expectException(ClientException::class);
        $this->apiClient->getAccount('1');
    }
}
