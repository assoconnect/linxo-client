<?php

declare(strict_types=1);

namespace AssoConnect\LinxoClient\Test;

use AssoConnect\LinxoClient\AuthClient;
use GuzzleHttp\HandlerStack;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Token\AccessTokenInterface;

class MockAuthClient extends AuthClient
{
    public const CODE = 'valid code';
    public const REFRESH_TOKEN = 'valid refresh token';
    public const ACCESS_TOKEN = 'valid refresh token';

    private MockMiddleware $mockMiddleware;

    public function __construct(string $clientId, string $clientSecret, string $redirectUri)
    {
        $this->mockMiddleware = new MockMiddleware();
        parent::__construct($clientId, $clientSecret, $redirectUri, false);
    }

    public function getMiddleware(): MockMiddleware
    {
        return $this->mockMiddleware;
    }

    protected function getGuzzleOptions(string $token): array
    {
        $options = parent::getGuzzleOptions($token);

        $stack = HandlerStack::create();
        $stack->push($this->mockMiddleware);
        $options['handler'] = $stack;

        return $options;
    }

    /**
     * Exchange a code for a token as part of the Authorization Code grant
     */
    public function getTokenFromCode(string $code, string $redirectUri): AccessTokenInterface
    {
        if (self::CODE === $code) {
            return $this->generateMockToken();
        }

        return parent::getTokenFromCode($code, $redirectUri);
    }

    /**
     * Get new tokens (access and refresh) from an existing refresh token
     */
    public function refreshToken(string $refreshToken): AccessTokenInterface
    {
        if (self::REFRESH_TOKEN === $refreshToken) {
            return $this->generateMockToken();
        }

        return parent::refreshToken($refreshToken);
    }

    private function generateMockToken(): AccessToken
    {
        return new AccessToken([
            'access_token' => self::ACCESS_TOKEN,
            'refresh_token' => self::REFRESH_TOKEN,
            'expires_in' => 3600,
        ]);
    }
}
