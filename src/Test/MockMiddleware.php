<?php

declare(strict_types=1);

namespace AssoConnect\LinxoClient\Test;

use AssoConnect\LinxoClient\ApiClient;
use GuzzleHttp\Promise as P;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\MockObject\Api;
use Psr\Http\Message\RequestInterface;

class MockMiddleware
{
    private ?array $me = null;
    private array $accounts = [];
    private array $transactions = [];

    public function __invoke(callable $handler): callable
    {
        return function (
            RequestInterface $request,
            array $options
        ) use ($handler) {
            $response = $this->findTheRightResponse($request);

            if (null !== $response) {
                if ($response instanceof \Throwable) {
                    return P\Create::rejectionFor($response);
                }
                return P\Create::promiseFor($response);
            }

            return $handler($request, $options);
        };
    }

    private function buildResponse(array $body): Response
    {
        return new Response(200, ['content-type' => 'json'], json_encode($body));
    }

    private function findTheRightResponse(RequestInterface $request): ?Response
    {
        $path = $request->getUri()->getPath();

        // ACCOUNTS
        if (strpos($path, '/' . ApiClient::VERSION . '/accounts') === 0) {
            return $this->respondToAccountsRequest($request);
        }

        // TRANSACTIONS
        if (strpos($path, '/' . ApiClient::VERSION . '/transactions') === 0) {
            return $this->respondToTransactionsRequest($request);
        }

        // USERS
        if (strpos($path, '/' . ApiClient::VERSION . '/users') === 0) {
            return $this->respondToUsersRequest();
        }

        return null;
    }

    private function respondToAccountsRequest(RequestInterface $request): ?Response
    {
        $path = $request->getUri()->getPath();

        if (preg_match('#/accounts/(\d+)$#', $path, $matches)) {
            $accountId = $matches[1];
            if (array_key_exists($accountId, $this->accounts)) {
                return $this->buildResponse($this->accounts[$accountId]);
            }
            return null;
        }

        return $this->buildResponse($this->accounts);
    }

    private function respondToTransactionsRequest(RequestInterface $request): ?Response
    {
        $path = $request->getUri()->getPath();

        if (preg_match('#/transactions/(\d+)$#', $path, $matches)) {
            $transactionId = $matches[1];
            if (array_key_exists($transactionId, $this->transactions)) {
                return $this->buildResponse($this->transactions[$transactionId]);
            }
            return null;
        }

        $filtered = $this->transactions;

        parse_str($request->getUri()->getQuery(), $query);
        if (array_key_exists('account_id', $query)) {
            $filtered = array_filter($filtered, function (array $transaction) use ($query): bool {
                return $query['account_id'] === $transaction['account_id'];
            });
        }
        if (array_key_exists('start_date', $query)) {
            $filtered = array_filter($filtered, function (array $transaction) use ($query): bool {
                return $query['start_date'] <= $transaction['date'];
            });
        }
        if (array_key_exists('end_date', $query)) {
            $filtered = array_filter($filtered, function (array $transaction) use ($query): bool {
                return $transaction['date'] <= $query['end_date'];
            });
        }
        if (array_key_exists('limit', $query) && array_key_exists('page', $query)) {
            $filtered = array_slice($filtered, $query['limit'] * ($query['page'] - 1), intval($query['limit']));
        }

        return $this->buildResponse($filtered);
    }

    private function respondToUsersRequest(): ?Response
    {
        if (null === $this->me) {
            return null;
        }
        return $this->buildResponse($this->me);
    }

    public function stackMe(array $me): void
    {
        $this->me = $me;
    }

    public function stackAccount(array $account): void
    {
        $this->accounts[$account['id']] = $account;
    }

    public function stackTransaction(array $transaction): void
    {
        $this->transactions[$transaction['id']] = $transaction;
    }
}
