<?php

declare(strict_types=1);

namespace AssoConnect\LinxoClient\Dto;

use Money\Currency;
use Money\Money;
use Symfony\Component\Translation\TranslatableMessage;

/**
 * @phpstan-type Account array{
 *     id: string,
 *     connection_id: string,
 *     name?: string,
 *     account_number?: string,
 *     iban?: string,
 *     status: string,
 *     balance: array{amount: array{amount: string, currency: string}},
 *     type?: string
 * }
 */
class AccountDto
{
    private string $id;
    private string $connectionId;
    private string $name;
    private ?string $iban;
    private string $status;
    private Money $balance;
    private Currency $currency;
    /** @var mixed[] */
    private array $data;

    /**
     * Possible account status
     * @link https://developers.linxo.com/reference-accounts-api/#section/Account-statuses
     * @see translations/linxo.intl-icu.[locale].yml
     */
    public const STATUS_MANUAL = 'MANUAL';
    public const STATUS_ACTIVE = 'ACTIVE';
    public const STATUS_ERROR = 'ERROR';
    public const STATUS_NOT_FOUND = 'NOT_FOUND';
    public const STATUS_CLOSED = 'CLOSED';
    public const STATUS_SUSPENDED = 'SUSPENDED';
    public const STATUS_PENDING_CONSENT = 'PENDING_CONSENT';

    /**
     * @param Account $data
     */
    public function __construct(array $data)
    {
        $this->id = $data['id'];
        $this->connectionId = $data['connection_id'];
        $this->name = $data['name'] ?? $data['account_number'];
        $this->iban = $data['iban'] ?? null;
        $this->status = $data['status'];
        $this->currency = new Currency($data['balance']['amount']['currency']);
        $this->balance = new Money(intval(round((float) $data['balance']['amount']['amount'] * 100)), $this->currency);
        $this->data = $data;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getConnectionId(): string
    {
        return $this->connectionId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getIban(): ?string
    {
        return $this->iban;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getLocalizedStatus(): TranslatableMessage
    {
        return new TranslatableMessage('account_status.' . $this->status, [], 'linxo');
    }

    public function getBalance(): Money
    {
        return $this->balance;
    }

    public function getCurrency(): Currency
    {
        return $this->currency;
    }

    /**
     * @codeCoverageIgnore
     * @return Account
     */
    public function getData(): array
    {
        return $this->data;
    }
}
