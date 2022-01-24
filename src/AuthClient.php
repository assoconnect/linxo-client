<?php

declare(strict_types=1);

namespace AssoConnect\LinxoClient;

use GuzzleHttp\Client;
use Koriym\HttpConstants\MediaType;
use Koriym\HttpConstants\RequestHeader;
use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Token\AccessTokenInterface;

class AuthClient extends GenericProvider
{
    private const ENDPOINT_API = [
        self::ENV_PROD => 'https://api.linxo.com',
        self::ENV_SANDBOX => 'https://sandbox-api.linxo.com',
    ];

    private const ENDPOINT_AUTH = [
        self::ENV_PROD => 'https://auth.linxo.com',
        self::ENV_SANDBOX => 'https://sandbox-auth.linxo.com',
    ];

    private const ENDPOINT_WEB = [
        self::ENV_PROD => 'https://wwws.linxo.com/auth.page',
        self::ENV_SANDBOX => 'https://sandbox-wwws.linxo.com/auth.page',
    ];

    private const ENV_SANDBOX = 'sandbox';
    private const ENV_PROD = 'prod';

    private string $apiEndpoint;
    private string $webEndpoint;

    public function __construct(
        string $clientId,
        string $clientSecret,
        string $redirectUri,
        bool $isProd
    ) {
        $this->apiEndpoint = self::ENDPOINT_API[$isProd ? self::ENV_PROD : self::ENV_SANDBOX];
        $this->webEndpoint = self::ENDPOINT_WEB[$isProd ? self::ENV_PROD : self::ENV_SANDBOX];

        parent::__construct([
            'clientId' => $clientId,
            'clientSecret' => $clientSecret,
            'redirectUri' => $redirectUri,
            'urlAccessToken' => self::ENDPOINT_AUTH[$isProd ? self::ENV_PROD : self::ENV_SANDBOX] . '/token',
            'urlAuthorize' => self::ENDPOINT_AUTH[$isProd ? self::ENV_PROD : self::ENV_SANDBOX] . '/signin',
            'urlResourceOwnerDetails' => '',
        ]);
    }

    /**
     * Create a Guzzle-powered API Client from a valid access token
     */
    public function createApiClient(string $token): ApiClient
    {
        return new ApiClient(new Client($this->getGuzzleOptions($token)));
    }

    protected function getGuzzleOptions(string $token): array
    {
        return [
            'base_uri' => $this->apiEndpoint,
            'headers' => [
                RequestHeader::AUTHORIZATION => 'Bearer ' . $token,
                RequestHeader::ACCEPT => MediaType::APPLICATION_JSON,
            ],
        ];
    }

    /**
     * Exchange a code for a token as part of the Authorization Code grant
     */
    public function getTokenFromCode(string $code): AccessTokenInterface
    {
        return $this->getAccessToken('authorization_code', ['code' => $code]);
    }

    /**
     * Get new tokens (access and refresh) from an existing refresh token
     */
    public function refreshToken(string $refreshToken): AccessTokenInterface
    {
        return $this->getAccessToken('refresh_token', ['refresh_token' => $refreshToken]);
    }

    /**
     * Web endpoint to signup on Linxo as an end-user
     */
    public function getWebEndpointSignup(): string
    {
        return $this->webEndpoint . '#Information';
    }

    /**
     * Web endpoint to log in Linxo as an end-user
     */
    public function getWebEndpointLogin(): string
    {
        return $this->webEndpoint . '#Login';
    }
}
