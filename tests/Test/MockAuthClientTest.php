<?php

declare(strict_types=1);

namespace AssoConnect\LinxoClient\Tests\Test;

use AssoConnect\LinxoClient\Test\MockAuthClient;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use PHPUnit\Framework\TestCase;

class MockAuthClientTest extends TestCase
{
    private MockAuthClient $authClient;

    public function setUp(): void
    {
        $this->authClient = new MockAuthClient(
            'client id',
            'client secret',
            'http://linxo.com'
        );
    }

    public function testGetTokenFromCodeIsOverridden(): void
    {
        $token = $this->authClient->getTokenFromCode(MockAuthClient::CODE);

        self::assertSame(MockAuthClient::ACCESS_TOKEN, $token->getToken());
        self::assertSame(MockAuthClient::REFRESH_TOKEN, $token->getRefreshToken());
    }

    public function testGetTokenFromCodeIsNotOverriden(): void
    {
        $this->expectException(IdentityProviderException::class);
        $this->authClient->getTokenFromCode('not mocked code');
    }

    public function testRefreshTokenIsOverridden(): void
    {
        $token = $this->authClient->refreshToken(MockAuthClient::REFRESH_TOKEN);

        self::assertSame(MockAuthClient::ACCESS_TOKEN, $token->getToken());
        self::assertSame(MockAuthClient::REFRESH_TOKEN, $token->getRefreshToken());
    }

    public function testRefreshTokenIsNotOverriden(): void
    {
        $this->expectException(IdentityProviderException::class);
        $this->authClient->refreshToken('not mocked code');
    }
}
