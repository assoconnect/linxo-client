<?php

declare(strict_types=1);

namespace AssoConnect\LinxoClient\Tests\Dto;

use AssoConnect\LinxoClient\Dto\AccountDto;
use Money\Currency;
use Money\Money;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Translation\TranslatableMessage;

class AccountDtoTest extends TestCase
{
    public function testConstructorSetsAllProperties(): void
    {
        $data = [
            'id' => 'acc-123',
            'connection_id' => 'conn-456',
            'name' => 'My Checking Account',
            'iban' => 'FR7630001007941234567890185',
            'status' => AccountDto::STATUS_ACTIVE,
            'balance' => [
                'amount' => [
                    'amount' => '1234.56',
                    'currency' => 'EUR',
                ],
            ],
        ];

        $dto = new AccountDto($data);

        self::assertSame('acc-123', $dto->getId());
        self::assertSame('conn-456', $dto->getConnectionId());
        self::assertSame('My Checking Account', $dto->getName());
        self::assertSame('FR7630001007941234567890185', $dto->getIban());
        self::assertSame(AccountDto::STATUS_ACTIVE, $dto->getStatus());
    }

    public function testBalanceConvertedToMoneyInCents(): void
    {
        $data = $this->createBaseData(['balance' => [
            'amount' => [
                'amount' => '1234.56',
                'currency' => 'EUR',
            ],
        ]]);

        $dto = new AccountDto($data);

        self::assertInstanceOf(Money::class, $dto->getBalance());
        self::assertSame('123456', $dto->getBalance()->getAmount());
        self::assertInstanceOf(Currency::class, $dto->getCurrency());
        self::assertSame('EUR', $dto->getCurrency()->getCode());
    }

    public function testBalanceWithZeroAmount(): void
    {
        $data = $this->createBaseData(['balance' => [
            'amount' => [
                'amount' => '0',
                'currency' => 'EUR',
            ],
        ]]);

        $dto = new AccountDto($data);

        self::assertSame('0', $dto->getBalance()->getAmount());
    }

    public function testBalanceWithNegativeAmount(): void
    {
        $data = $this->createBaseData(['balance' => [
            'amount' => [
                'amount' => '-500.25',
                'currency' => 'EUR',
            ],
        ]]);

        $dto = new AccountDto($data);

        self::assertSame('-50025', $dto->getBalance()->getAmount());
    }

    public function testBalanceRoundingWithMoreThanTwoDecimals(): void
    {
        $data = $this->createBaseData(['balance' => [
            'amount' => [
                'amount' => '100.999',
                'currency' => 'EUR',
            ],
        ]]);

        $dto = new AccountDto($data);

        self::assertSame('10100', $dto->getBalance()->getAmount());
    }

    public function testNameFallsBackToAccountNumber(): void
    {
        $data = $this->createBaseData([
            'name' => null,
            'account_number' => 'FR76123456789',
        ]);
        unset($data['name']);

        $dto = new AccountDto($data);

        self::assertSame('FR76123456789', $dto->getName());
    }

    public function testNameUsedWhenProvided(): void
    {
        $data = $this->createBaseData([
            'name' => 'Primary Account',
            'account_number' => 'FR76123456789',
        ]);

        $dto = new AccountDto($data);

        self::assertSame('Primary Account', $dto->getName());
    }

    public function testIbanIsNullWhenNotProvided(): void
    {
        $data = $this->createBaseData();
        unset($data['iban']);

        $dto = new AccountDto($data);

        self::assertNull($dto->getIban());
    }

    public function testDifferentCurrencies(): void
    {
        $currencies = ['USD', 'GBP', 'CHF', 'JPY'];

        foreach ($currencies as $currencyCode) {
            $data = $this->createBaseData(['balance' => [
                'amount' => [
                    'amount' => '100.00',
                    'currency' => $currencyCode,
                ],
            ]]);

            $dto = new AccountDto($data);

            self::assertSame($currencyCode, $dto->getCurrency()->getCode());
        }
    }

    public function testGetLocalizedStatusReturnsTranslatableMessage(): void
    {
        $data = $this->createBaseData(['status' => AccountDto::STATUS_ACTIVE]);

        $dto = new AccountDto($data);
        $message = $dto->getLocalizedStatus();

        self::assertInstanceOf(TranslatableMessage::class, $message);
        self::assertSame('account_status.ACTIVE', $message->getMessage());
        self::assertSame('linxo', $message->getDomain());
    }

    /**
     * @dataProvider statusProvider
     */
    public function testAllStatusConstants(string $status): void
    {
        $data = $this->createBaseData(['status' => $status]);

        $dto = new AccountDto($data);

        self::assertSame($status, $dto->getStatus());
        self::assertSame('account_status.' . $status, $dto->getLocalizedStatus()->getMessage());
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function statusProvider(): iterable
    {
        yield 'manual' => [AccountDto::STATUS_MANUAL];
        yield 'active' => [AccountDto::STATUS_ACTIVE];
        yield 'error' => [AccountDto::STATUS_ERROR];
        yield 'not_found' => [AccountDto::STATUS_NOT_FOUND];
        yield 'closed' => [AccountDto::STATUS_CLOSED];
        yield 'suspended' => [AccountDto::STATUS_SUSPENDED];
        yield 'pending_consent' => [AccountDto::STATUS_PENDING_CONSENT];
    }

    /**
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    private function createBaseData(array $overrides = []): array
    {
        return array_merge([
            'id' => 'acc-123',
            'connection_id' => 'conn-456',
            'name' => 'Test Account',
            'iban' => 'FR7630001007941234567890185',
            'status' => AccountDto::STATUS_ACTIVE,
            'balance' => [
                'amount' => [
                    'amount' => '100.00',
                    'currency' => 'EUR',
                ],
            ],
        ], $overrides);
    }
}
