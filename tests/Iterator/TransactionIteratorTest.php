<?php

declare(strict_types=1);

namespace AssoConnect\LinxoClient\Tests\Iterator;

use AssoConnect\LinxoClient\ApiClient;
use AssoConnect\LinxoClient\Iterator\TransactionIterator;
use AssoConnect\PHPDate\AbsoluteDate;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Money\Money;
use PHPUnit\Framework\TestCase;

class TransactionIteratorTest extends TestCase
{
    public function testIteratorWorks(): void
    {
        $mock = new MockHandler([
            new Response(200, [], file_get_contents(__DIR__ . '/../responses/transactions.1.json')),
            new Response(200, [], file_get_contents(__DIR__ . '/../responses/transactions.2.json')),
            new Response(200, [], file_get_contents(__DIR__ . '/../responses/transactions.3.json')),
        ]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);
        $apiClient = new ApiClient($client, 'some user id');

        $iterator = $apiClient->getTransactionsIterator('some id');

        $transactions = [];
        foreach ($iterator as $key => $transaction) {
            $transactions[$key] = $transaction;
        }

        self::assertCount(4, $transactions);
        // There are only 2 (instead of 100) transactions per mock file
        self::assertSame([0, 1, 100, 101], array_keys($transactions));

        self::assertSame('2433482741', $transactions[0]->getId());
        self::assertSame('2431931741', $transactions[1]->getId());
        self::assertSame('2433482742', $transactions[100]->getId());
        self::assertSame('2431931742', $transactions[101]->getId());
    }
}
