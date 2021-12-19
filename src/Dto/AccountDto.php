<?php

declare(strict_types=1);

namespace AssoConnect\LinxoClient\Dto;

use Money\Currency;

class AccountDto
{
    private string $id;
    private string $connectionId;
    private string $name;
    private string $iban;
    private string $status;
    private Currency $currency;

    /**
     * Possible account status
     * @link https://developers.linxo.com/reference-accounts-api/#section/Account-statuses
     */
    public const STATUS_MANUAL = 'MANUAL';
    public const STATUS_ACTIVE = 'ACTIVE';
    public const STATUS_ERROR = 'ERROR';
    public const STATUS_NOT_FOUND = 'NOT_FOUND';
    public const STATUS_CLOSED = 'CLOSED';
    public const STATUS_SUSPENDED = 'SUSPENDED';
    public const STATUS_PENDING_CONSENT = 'PENDING_CONSENT';

    public function __construct(array $data)
    {
        $this->id = $data['id'];
        $this->connectionId = $data['connection_id'];
        $this->name = $data['name'];
        $this->iban = $data['iban'];
        $this->status = $data['status'];
        $this->currency = new Currency($data['currency']);
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

    public function getIban(): string
    {
        return $this->iban;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getCurrency(): Currency
    {
        return $this->currency;
    }
}
