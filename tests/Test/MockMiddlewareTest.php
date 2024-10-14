<?php

declare(strict_types=1);

namespace AssoConnect\LinxoClient\Tests\Test;

use AssoConnect\LinxoClient\ApiClient;
use AssoConnect\LinxoClient\Test\MockAuthClient;
use AssoConnect\LinxoClient\Test\MockFactory;
use AssoConnect\PHPDate\AbsoluteDate;
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
            'https://linxo.com'
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

    public function testGetMeIsOverridden(): void
    {
        $this->factory->mockMe();
        $me = $this->apiClient->getCurrentUser();

        self::assertSame('1', $me->getId());
    }

    public function testGetMeIsNotOverridden(): void
    {
        $this->expectException(ClientException::class);
        $this->apiClient->getCurrentUser();
    }

    public function testGetTransactionIsOverridden(): void
    {
        $this->factory->mockTransaction();

        self::assertSame('1', $this->apiClient->getTransactions('1')[0]->getId());
        self::assertSame([], $this->apiClient->getTransactions('1', 2));
        self::assertSame([], $this->apiClient->getTransactions('1', 1, new AbsoluteDate('2100-01-01')));
        self::assertSame([], $this->apiClient->getTransactions('1', 1, null, new AbsoluteDate('1970-01-01')));
    }
}
