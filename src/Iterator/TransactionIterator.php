<?php

declare(strict_types=1);

namespace AssoConnect\LinxoClient\Iterator;

use AssoConnect\LinxoClient\ApiClient;
use AssoConnect\LinxoClient\Dto\TransactionDto;
use AssoConnect\PHPDate\AbsoluteDate;

/**
 * Iterator to loop through all available transactions
 *
 * It browses through the API pagination system
 * @implements \Iterator<int, TransactionDto>
 */
class TransactionIterator implements \Iterator
{
    private ApiClient $apiClient;
    private string $accountId;
    private ?AbsoluteDate $startDate;
    private ?AbsoluteDate $endDate;

    /** @var TransactionDto[]|null */
    private ?array $transactions;
    private int $currentCursor;
    private int $currentPage;

    private const LIMIT = 100;

    public function __construct(
        ApiClient $apiClient,
        string $accountId,
        ?AbsoluteDate $startDate = null,
        ?AbsoluteDate $endDate = null
    ) {
        $this->apiClient = $apiClient;
        $this->accountId = $accountId;
        $this->startDate = $startDate;
        $this->endDate = $endDate;

        $this->transactions = null;
        $this->currentPage = 1;
        $this->currentCursor = 0;
    }

    public function rewind(): void
    {
        if ($this->currentPage !== 1) {
            $this->currentPage = 1;
            $this->transactions = null;
        }
        $this->currentCursor = 0;
    }

    public function valid(): bool
    {
        // First call
        if (null === $this->transactions) {
            $this->transactions = $this->apiClient->getTransactions(
                $this->accountId,
                $this->currentPage,
                $this->startDate,
                $this->endDate,
                self::LIMIT
            );
        }

        // The current page is empty in two cases
        // - first page and no transactions match the search criteria
        // - second+ page and no no more transactions match the search criteria
        if ([] === $this->transactions) {
            return false;
        }

        // The current page can be used
        if ($this->currentCursor < count($this->transactions)) {
            return true;
        }

        // Fetch the next page
        $this->currentPage++;
        $this->currentCursor = 0;
        $this->transactions = null;
        // Then try again with the next page
        return $this->valid();
    }

    public function key(): int
    {
        return self::LIMIT * ($this->currentPage - 1) + $this->currentCursor;
    }

    public function current(): TransactionDto
    {
        return $this->transactions[$this->currentCursor];
    }

    public function next(): void
    {
        $this->currentCursor++;
    }
}
