<?php

declare(strict_types=1);

namespace AssoConnect\LinxoClient;

use AssoConnect\LinxoClient\Dto\AccountDto;
use AssoConnect\LinxoClient\Dto\ConnectionDto;
use AssoConnect\LinxoClient\Dto\TransactionDto;
use AssoConnect\LinxoClient\Dto\UserDto;
use AssoConnect\LinxoClient\Iterator\TransactionIterator;
use AssoConnect\PHPDate\AbsoluteDate;
use GuzzleHttp\ClientInterface;
use Koriym\HttpConstants\Method;

class ApiClient
{
    private ClientInterface $client;

    public const VERSION = 'v2.1';

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    private function request(string $uri, array $query = []): array
    {
        $response = $this->client->request(Method::GET, '/' . self::VERSION . $uri, ['query' => $query]);
        return json_decode($response->getBody()->__toString(), true);
    }

    /**
     * Return the current logged-in user
     * @link https://developers.linxo.com/reference-accounts-api#operation/getUser
     */
    public function getCurrentUser(): UserDto
    {
        return new UserDto($this->request('/users/me'));
    }

    /**
     * @link https://developers.linxo.com/reference-accounts-api#operation/getConnectionsUsingGET
     * @return ConnectionDto[]
     */
    public function getConnections(): iterable
    {
        return array_map(function ($connection): ConnectionDto {
            return new ConnectionDto($connection);
        }, $this->request('/connections'));
    }

    public function getConnection(string $connectionId): ConnectionDto
    {
        return new ConnectionDto($this->request('/connections/' . $connectionId));
    }

    /**
     * @link https://developers.linxo.com/reference-accounts-api#operation/getAccounts
     * @return AccountDto[]
     */
    public function getAccounts(): iterable
    {
        return array_map(function ($account): AccountDto {
            return new AccountDto($account);
        }, $this->request('/accounts'));
    }

    /**
     * @link https://developers.linxo.com/reference-accounts-api#operation/getAccount
     */
    public function getAccount(string $id): AccountDto
    {
        return new AccountDto($this->request('/accounts/' . $id));
    }

    /**
     * @link https://developers.linxo.com/reference-accounts-api#operation/getTransactions
     * @return TransactionDto[]
     */
    public function getTransactions(
        string $accountId,
        int $page = 1,
        AbsoluteDate $startDate = null,
        AbsoluteDate $endDate = null,
        int $limit = 100
    ): iterable {
        $query = [
            'account_id' => $accountId,
            'page' => $page,
            'limit' => $limit
        ];

        if (null !== $startDate) {
            $query['start_date'] = $startDate->startsAt(new \DateTimeZone('Europe/Paris'))->getTimestamp();
        }

        if (null !== $endDate) {
            $query['end_date'] = $endDate->startsAt(new \DateTimeZone('Europe/Paris'))->getTimestamp();
        }

        $transactions = $this->request('/transactions', $query);
        return array_map(function ($transaction): TransactionDto {
            return new TransactionDto($transaction);
        }, $transactions);
    }

    /**
     * Return an iterator to loop through all transactions
     */
    public function getTransactionsIterator(
        string $accountId,
        AbsoluteDate $startDate = null,
        AbsoluteDate $endDate = null
    ): TransactionIterator {
        return new TransactionIterator($this, $accountId, $startDate, $endDate);
    }
}
