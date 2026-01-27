<?php

declare(strict_types=1);

namespace AssoConnect\LinxoClient\Tests\Dto;

use AssoConnect\LinxoClient\Dto\TransactionDto;
use AssoConnect\PHPDate\AbsoluteDate;
use Money\Currency;
use Money\Money;
use PHPUnit\Framework\TestCase;

class TransactionDtoTest extends TestCase
{
    public function testConstructorSetsAllProperties(): void
    {
        $data = [
            'id' => 'txn-123',
            'account_id' => 'acc-456',
            'amount' => [
                'amount' => '150.75',
                'currency' => 'EUR',
            ],
            'enrichments' => [
                'display_label' => 'Grocery Store Purchase',
                'date' => '2024-03-15T10:30:00+01:00',
            ],
            'notes' => 'Weekly groceries',
            'type' => TransactionDto::TYPE_POINT_OF_SALE,
        ];

        $dto = new TransactionDto($data);

        $this->assertSame('txn-123', $dto->getId());
        $this->assertSame('acc-456', $dto->getAccountId());
        $this->assertSame('Grocery Store Purchase', $dto->getLabel());
        $this->assertSame('Weekly groceries', $dto->getNotes());
        $this->assertSame(TransactionDto::TYPE_POINT_OF_SALE, $dto->getType());
    }

    public function testAmountConvertedToMoneyInCents(): void
    {
        $data = $this->createBaseData(['amount' => [
            'amount' => '1234.56',
            'currency' => 'EUR',
        ]]);

        $dto = new TransactionDto($data);

        $this->assertInstanceOf(Money::class, $dto->getAmount());
        $this->assertSame('123456', $dto->getAmount()->getAmount());
        $this->assertInstanceOf(Currency::class, $dto->getAmount()->getCurrency());
        $this->assertSame('EUR', $dto->getAmount()->getCurrency()->getCode());
    }

    public function testAmountWithNegativeValue(): void
    {
        $data = $this->createBaseData(['amount' => [
            'amount' => '-99.99',
            'currency' => 'EUR',
        ]]);

        $dto = new TransactionDto($data);

        $this->assertSame('-9999', $dto->getAmount()->getAmount());
    }

    public function testAmountWithZeroValue(): void
    {
        $data = $this->createBaseData(['amount' => [
            'amount' => '0',
            'currency' => 'EUR',
        ]]);

        $dto = new TransactionDto($data);

        $this->assertSame('0', $dto->getAmount()->getAmount());
    }

    public function testAmountRoundingWithMoreThanTwoDecimals(): void
    {
        $data = $this->createBaseData(['amount' => [
            'amount' => '50.555',
            'currency' => 'EUR',
        ]]);

        $dto = new TransactionDto($data);

        $this->assertSame('5056', $dto->getAmount()->getAmount());
    }

    public function testTypeFallsBackToOtherWhenNotProvided(): void
    {
        $data = $this->createBaseData();
        unset($data['type']);

        $dto = new TransactionDto($data);

        $this->assertSame(TransactionDto::TYPE_OTHER, $dto->getType());
    }

    public function testLabelIsNullWhenNotProvided(): void
    {
        $data = $this->createBaseData();
        unset($data['enrichments']['display_label']);

        $dto = new TransactionDto($data);

        $this->assertNull($dto->getLabel());
    }

    public function testNotesIsNullWhenNotProvided(): void
    {
        $data = $this->createBaseData();
        unset($data['notes']);

        $dto = new TransactionDto($data);

        $this->assertNull($dto->getNotes());
    }

    public function testDateParsedAsAbsoluteDate(): void
    {
        $data = $this->createBaseData(['enrichments' => [
            'display_label' => 'Test',
            'date' => '2024-06-20T14:30:00+02:00',
        ]]);

        $dto = new TransactionDto($data);

        $this->assertInstanceOf(AbsoluteDate::class, $dto->getDate());
        $this->assertSame('2024-06-20', $dto->getDate()->__toString());
    }

    public function testDateUsesEuropeParisTimezone(): void
    {
        $this->assertSame('Europe/Paris', TransactionDto::TIMEZONE);

        // Test a date that would be different in UTC vs Europe/Paris
        // 2024-01-15T23:30:00Z (UTC) is 2024-01-16T00:30:00 in Paris (winter time, UTC+1)
        $data = $this->createBaseData(['enrichments' => [
            'display_label' => 'Test',
            'date' => '2024-01-15T23:30:00Z',
        ]]);

        $dto = new TransactionDto($data);

        // In Europe/Paris, this would be the next day
        $this->assertSame('2024-01-16', $dto->getDate()->__toString());
    }

    public function testDifferentCurrencies(): void
    {
        $currencies = ['USD', 'GBP', 'CHF', 'JPY'];

        foreach ($currencies as $currencyCode) {
            $data = $this->createBaseData(['amount' => [
                'amount' => '100.00',
                'currency' => $currencyCode,
            ]]);

            $dto = new TransactionDto($data);

            $this->assertSame($currencyCode, $dto->getAmount()->getCurrency()->getCode());
        }
    }

    /**
     * @dataProvider transactionTypeProvider
     */
    public function testAllTransactionTypeConstants(string $type): void
    {
        $data = $this->createBaseData(['type' => $type]);

        $dto = new TransactionDto($data);

        $this->assertSame($type, $dto->getType());
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function transactionTypeProvider(): iterable
    {
        yield 'credit' => [TransactionDto::TYPE_CREDIT];
        yield 'debit' => [TransactionDto::TYPE_DEBIT];
        yield 'interest' => [TransactionDto::TYPE_INTEREST];
        yield 'dividend' => [TransactionDto::TYPE_DIVIDEND];
        yield 'bank_fee' => [TransactionDto::TYPE_BANK_FEE];
        yield 'deposit' => [TransactionDto::TYPE_DEPOSIT];
        yield 'atm' => [TransactionDto::TYPE_ATM];
        yield 'point_of_sale' => [TransactionDto::TYPE_POINT_OF_SALE];
        yield 'credit_card_payment' => [TransactionDto::TYPE_CREDIT_CARD_PAYMENT];
        yield 'internal_transfer' => [TransactionDto::TYPE_INTERNAL_TRANSFER];
        yield 'potential_transfer' => [TransactionDto::TYPE_POTENTIAL_TRANSFER];
        yield 'check' => [TransactionDto::TYPE_CHECK];
        yield 'electronic_payment' => [TransactionDto::TYPE_ELECTRONIC_PAYMENT];
        yield 'cash' => [TransactionDto::TYPE_CASH];
        yield 'direct_deposit' => [TransactionDto::TYPE_DIRECT_DEPOSIT];
        yield 'direct_debit' => [TransactionDto::TYPE_DIRECT_DEBIT];
        yield 'repeating_payment' => [TransactionDto::TYPE_REPEATING_PAYMENT];
        yield 'other' => [TransactionDto::TYPE_OTHER];
    }

    /**
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    private function createBaseData(array $overrides = []): array
    {
        $base = [
            'id' => 'txn-123',
            'account_id' => 'acc-456',
            'amount' => [
                'amount' => '100.00',
                'currency' => 'EUR',
            ],
            'enrichments' => [
                'display_label' => 'Test Transaction',
                'date' => '2024-03-15T10:00:00+01:00',
            ],
            'notes' => 'Test notes',
            'type' => TransactionDto::TYPE_CREDIT,
        ];

        // Handle nested merges for enrichments
        if (isset($overrides['enrichments'])) {
            $base['enrichments'] = array_merge($base['enrichments'], $overrides['enrichments']);
            unset($overrides['enrichments']);
        }

        return array_merge($base, $overrides);
    }
}
