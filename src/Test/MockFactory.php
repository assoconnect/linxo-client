<?php

declare(strict_types=1);

namespace AssoConnect\LinxoClient\Test;

use AssoConnect\LinxoClient\ApiClient;
use AssoConnect\LinxoClient\Dto\AccountDto;
use AssoConnect\LinxoClient\Dto\TransactionDto;
use AssoConnect\LinxoClient\Dto\UserDto;
use AssoConnect\PHPDate\AbsoluteDate;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use function _PHPStan_70b6e53dc\RingCentral\Psr7\build_query;

class MockFactory
{
    public const AUTHORIZATION_CODE = '';
    public const OLD_REFRESH_TOKEN = '';

    private const ENDPOINT = 'https://sandbox-api.linxo.com';

    private MockMiddleware $middleware;

    public function __construct(MockMiddleware $middleware)
    {
        $this->middleware = $middleware;
    }

    public function mockMe(array $body = []): array
    {
        $body = array_merge([
            'id' => '1',
            'email' => 'john.doe@gmail.com',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'creation_date' => mktime(12, 0, 0, 1, 1, 2020),
        ], $body);
        $this->middleware->stackMe($body);

        return $body;
    }

    public function mockAccount(array $body = []): array
    {
        $body = array_merge([
            'id' => '1',
            'connection_id' => '1',
            'name' => 'My account',
            'iban' => 'FR0512739000308643578317D43',
            'status' => AccountDto::STATUS_ACTIVE,
            'currency' => 'EUR',
        ], $body);

        $this->middleware->stackAccount($body);
        return $body;
    }

    public function mockTransaction(array $body = []): array
    {
        $body = array_merge([
            'id' => '1',
            'account_id' => '1',
            'amount' => '100',
            'currency' => 'EUR',
            'label' => 'FACTURE EDF',
            'type' => TransactionDto::TYPE_CREDIT,
            'date' => AbsoluteDate::createRelative('2 days ago'),
        ], $body);
        $body['date'] = $body['date']->startsAt(new \DateTimeZone(TransactionDto::TIMEZONE))->getTimestamp();
        $this->middleware->stackTransaction($body);
        return $body;
    }
}
