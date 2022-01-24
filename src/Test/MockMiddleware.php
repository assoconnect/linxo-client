<?php

declare(strict_types=1);

namespace AssoConnect\LinxoClient\Test;

use GuzzleHttp\Promise as P;
use GuzzleHttp\Psr7\Response;
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

    private function findTheRightResponse(RequestInterface $request)
    {
        $path = $request->getUri()->getPath();

        // USERS
        if (strpos($path, '/users/me') === 0) {
            if (null === $this->me) {
                return null;
            }
            return $this->buildResponse($this->me);
        }

        // ACCOUNTS
        if (strpos($path, '/accounts/') === 0) {
            $accountId = substr($path, 10);
            if (array_key_exists($accountId, $this->accounts)) {
                return $this->buildResponse($this->accounts[$accountId]);
            }
            return null;
        }
        if (strpos($path, '/accounts') === 0) {
            return $this->buildResponse($this->accounts);
        }

        // TRANSACTIONS
        if (strpos($path, '/transactions/') === 0) {
            $transactionId = substr($path, 14);
            if (array_key_exists($transactionId, $this->transactions)) {
                return $this->buildResponse($this->transactions[$transactionId]);
            }
            return null;
        }

        if (strpos($path, '/transactions') === 0) {
            $transactions = $this->transactions;

            parse_str($request->getUri()->getQuery(), $query);
            if (array_key_exists('account_id', $query)) {
                $transactions = array_filter($transactions, function (array $transaction) use ($query): bool {
                    return $query['account_id'] === $transaction['account_id'];
                });
            }
            if (array_key_exists('start_date', $query)) {
                $transactions = array_filter($transactions, function (array $transaction) use ($query): bool {
                    return $query['start_date'] <= $transaction['start_date'];
                });
            }
            if (array_key_exists('end_date', $query)) {
                $transactions = array_filter($transactions, function (array $transaction) use ($query): bool {
                    return $transaction['end_date'] <= $query['end_date'];
                });
            }
            if (array_key_exists('limit', $query) && array_key_exists('page', $query)) {
                $transactions = array_slice($transactions, $query['limit'] * ($query['page'] - 1), $query['limit']);
            }

            return $this->buildResponse($transactions);
        }

        return null;
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
