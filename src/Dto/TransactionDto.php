<?php

declare(strict_types=1);

namespace AssoConnect\LinxoClient\Dto;

use AssoConnect\PHPDate\AbsoluteDate;
use Money\Currency;
use Money\Money;
use Symfony\Component\Translation\TranslatableMessage;

class TransactionDto
{
    private string $id;
    private string $accountId;
    private Money $amount;
    private ?string $label;
    private ?string $notes;
    private string $type;
    private AbsoluteDate $date;

    /**
     * Possible transaction types
     * @link https://developers.linxo.com/reference-accounts-api/#operation/getTransaction
     * @see translations/linxo.intl-icu.[locale].yml
     */
    public const TYPE_CREDIT = 'CREDIT';
    public const TYPE_DEBIT = 'DEBIT';
    public const TYPE_INTEREST = 'INTEREST';
    public const TYPE_DIVIDEND = 'DIVIDEND';
    public const TYPE_BANK_FEE = 'BANK_FEE';
    public const TYPE_DEPOSIT = 'DEPOSIT';
    public const TYPE_ATM = 'ATM';
    public const TYPE_POINT_OF_SALE = 'POINT_OF_SALE';
    public const TYPE_CREDIT_CARD_PAYMENT = 'CREDIT_CARD_PAYMENT';
    public const TYPE_INTERNAL_TRANSFER = 'INTERNAL_TRANSFER';
    public const TYPE_POTENTIAL_TRANSFER = 'POTENTIAL_TRANSFER';
    public const TYPE_CHECK = 'CHECK';
    public const TYPE_ELECTRONIC_PAYMENT = 'ELECTRONIC_PAYMENT';
    public const TYPE_CASH = 'CASH';
    public const TYPE_DIRECT_DEPOSIT = 'DIRECT_DEPOSIT';
    public const TYPE_DIRECT_DEBIT = 'DIRECT_DEBIT';
    public const TYPE_REPEATING_PAYMENT = 'REPEATING_PAYMENT';
    public const TYPE_OTHER = 'OTHER';

    public function __construct(array $data)
    {
        $this->id = $data['id'];
        $this->accountId = $data['account_id'];
        $this->amount = new Money(intval($data['amount'] * 100), new Currency($data['currency']));
        $this->label = $data['label'] ?? null;
        $this->notes = $data['notes'] ?? null;
        $this->type = $data['type'];
        $this->date = AbsoluteDate::createInTimezone(
            // Linxo uses timestamps but their servers' timezone is Europe/Paris
            new \DateTimeZone('Europe/Paris'),
            new \DateTime('@' . $data['date'])
        );
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getAccountId(): string
    {
        return $this->accountId;
    }

    public function getAmount(): Money
    {
        return $this->amount;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getDate(): AbsoluteDate
    {
        return $this->date;
    }
}
