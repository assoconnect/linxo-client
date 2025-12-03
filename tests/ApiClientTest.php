<?php

declare(strict_types=1);

namespace AssoConnect\LinxoClient\Tests;

use AssoConnect\LinxoClient\ApiClient;
use AssoConnect\LinxoClient\AuthClient;
use AssoConnect\PHPDate\AbsoluteDate;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Money\Currency;
use Money\Money;
use PHPUnit\Framework\TestCase;

class ApiClientTest extends TestCase
{
    private const USER_ID = '3996384';

    /** @param Response[] $queue */
    private function mockApiCalls(array $queue): ApiClient
    {
        $mock = new MockHandler($queue);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);
        return new ApiClient($client, self::USER_ID);
    }

    public function testGetCurrentUserCorrectlyParseTheApiResponse(): void
    {
        $client = $this->mockApiCalls([
            new Response(200, [], file_get_contents(__DIR__ . '/responses/user.json')),
        ]);
        $user = $client->getCurrentUser();

        self::assertSame(self::USER_ID, $user->getId());
        self::assertSame('DOE', $user->getLastname());
        self::assertSame('John', $user->getFirstname());
        self::assertSame('john.doe@gmail.com', $user->getEmail());
        self::assertEquals(new \DateTimeImmutable('@1639524127'), $user->getCreatedAt());
    }

    public function testGetConnectionsCorrectlyParseTheApiResponse(): void
    {
        $client = $this->mockApiCalls([
            new Response(200, [], file_get_contents(__DIR__ . '/responses/connections.json')),
        ]);

        $connections = $client->getConnections();

        self::assertCount(1, $connections);

        self::assertSame('4422323', $connections[0]->getId());
        self::assertSame('Crédit Agricole - Languedoc', $connections[0]->getName());
        self::assertSame('SUCCESS', $connections[0]->getStatus());
        self::assertSame(
            'https://static.oxlin.io/common/pictures/providers_logos/1022.png',
            $connections[0]->getLogoUrl()
        );
    }

    public function testGetConnectionCorrectlyParseTheApiResponse(): void
    {
        $client = $this->mockApiCalls([
            new Response(200, [], file_get_contents(__DIR__ . '/responses/connection.json')),
        ]);

        $connection = $client->getConnection('some id');

        self::assertSame('4422323', $connection->getId());
        self::assertSame('Crédit Agricole - Languedoc', $connection->getName());
        self::assertSame('SUCCESS', $connection->getStatus());
        self::assertSame(
            'https://static.oxlin.io/common/pictures/providers_logos/1022.png',
            $connection->getLogoUrl()
        );
    }

    public function testGetAccountsCorrectlyParseTheApiResponse(): void
    {
        $client = $this->mockApiCalls([
            new Response(200, [], file_get_contents(__DIR__ . '/responses/accounts.json')),
        ]);

        $accounts = $client->getAccounts();

        self::assertCount(2, $accounts);

        self::assertSame('63723418', $accounts[0]->getId());
        self::assertSame('4422323', $accounts[0]->getConnectionId());
        self::assertSame('FR6230003000502376843574B66', $accounts[0]->getIban());
        self::assertSame('COMPTE COURANT - ASSNÎMES / CA', $accounts[0]->getName());
        self::assertSame('ACTIVE', $accounts[0]->getStatus());
        self::assertEquals(new Currency('EUR'), $accounts[0]->getCurrency());
        self::assertSame('244146', $accounts[0]->getBalance()->getAmount());

        self::assertSame('63723417', $accounts[1]->getId());
        self::assertSame('4422323', $accounts[1]->getConnectionId());
        self::assertSame('FR6517569000401628527762E63', $accounts[1]->getIban());
        self::assertSame('COMPTE COURANT - ASSNÎMES / Futsal', $accounts[1]->getName());
        self::assertSame('ACTIVE', $accounts[1]->getStatus());
        self::assertEquals(new Currency('EUR'), $accounts[1]->getCurrency());
        self::assertSame('76010', $accounts[1]->getBalance()->getAmount());
    }

    public function testGetAccountCorrectlyParseTheApiResponse(): void
    {
        $client = $this->mockApiCalls([
            new Response(200, [], file_get_contents(__DIR__ . '/responses/account.json')),
        ]);

        $account = $client->getAccount('some id');

        self::assertSame('63723418', $account->getId());
        self::assertSame('4422323', $account->getConnectionId());
        self::assertSame('FR6230003000502376843574B66', $account->getIban());
        self::assertSame('COMPTE COURANT / CA', $account->getName());
        self::assertSame('ACTIVE', $account->getStatus());
        self::assertEquals(new Currency('EUR'), $account->getCurrency());
        self::assertSame('244146', $account->getBalance()->getAmount());
    }

    public function testGetTransactionsCorrectlyParseTheApiResponse(): void
    {
        $client = $this->mockApiCalls([
            new Response(200, [], file_get_contents(__DIR__ . '/responses/transactions.1.json')),
        ]);

        $transactions = $client->getTransactions('some id');

        self::assertCount(2, $transactions);

        self::assertSame('2433482741', $transactions[0]->getId());
        self::assertSame('63723418', $transactions[0]->getAccountId());
        self::assertEquals(Money::EUR(-7999), $transactions[0]->getAmount());
        self::assertEquals(new AbsoluteDate('2021-12-20'), $transactions[0]->getDate());
        self::assertSame('France Télécom', $transactions[0]->getLabel());
        self::assertSame(
            "PRELEVEMENT France Télécom\nAbonnement France Télécom\na6487643\nSLMP020105989\nFR27001621234",
            $transactions[0]->getNotes()
        );
        self::assertSame('DIRECT_DEBIT', $transactions[0]->getType());

        self::assertSame('2431931741', $transactions[1]->getId());
        self::assertSame('63723418', $transactions[1]->getAccountId());
        self::assertEquals(Money::EUR(-400), $transactions[1]->getAmount());
        self::assertEquals(new AbsoluteDate('2021-12-17'), $transactions[1]->getDate());
        self::assertSame('FRAIS MANDAT SEPA N°2135100087457', $transactions[1]->getLabel());
        self::assertNull($transactions[1]->getNotes());
        self::assertSame('DEBIT', $transactions[1]->getType());
    }
}
