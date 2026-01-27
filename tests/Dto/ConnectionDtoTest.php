<?php

declare(strict_types=1);

namespace AssoConnect\LinxoClient\Tests\Dto;

use AssoConnect\LinxoClient\Dto\ConnectionDto;
use PHPUnit\Framework\TestCase;

class ConnectionDtoTest extends TestCase
{
    public function testConstructorSetsAllProperties(): void
    {
        $data = [
            'id' => 'conn-123',
            'name' => 'BNP Paribas',
            'status' => ConnectionDto::STATUS_SUCCESS,
            'provider_logo_url' => 'https://example.com/bnp-logo.png',
        ];

        $dto = new ConnectionDto($data);

        $this->assertSame('conn-123', $dto->getId());
        $this->assertSame('BNP Paribas', $dto->getName());
        $this->assertSame(ConnectionDto::STATUS_SUCCESS, $dto->getStatus());
        $this->assertSame('https://example.com/bnp-logo.png', $dto->getLogoUrl());
    }

    /**
     * @dataProvider statusProvider
     */
    public function testAllStatusConstants(string $status): void
    {
        $data = $this->createBaseData(['status' => $status]);

        $dto = new ConnectionDto($data);

        $this->assertSame($status, $dto->getStatus());
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function statusProvider(): iterable
    {
        yield 'running' => [ConnectionDto::STATUS_RUNNING];
        yield 'success' => [ConnectionDto::STATUS_SUCCESS];
        yield 'partial_success' => [ConnectionDto::STATUS_PARTIAL_SUCCESS];
        yield 'failed' => [ConnectionDto::STATUS_FAILED];
        yield 'closed' => [ConnectionDto::STATUS_CLOSED];
        yield 'none' => [ConnectionDto::STATUS_NONE];
    }

    public function testDifferentBankNames(): void
    {
        $banks = [
            'BNP Paribas',
            'Societe Generale',
            'Credit Agricole',
            'La Banque Postale',
        ];

        foreach ($banks as $bankName) {
            $data = $this->createBaseData(['name' => $bankName]);

            $dto = new ConnectionDto($data);

            $this->assertSame($bankName, $dto->getName());
        }
    }

    public function testLogoUrlWithVariousFormats(): void
    {
        $urls = [
            'https://cdn.linxo.com/logos/bnp.png',
            'https://example.com/path/to/logo.svg',
            'https://secure.bank.com/images/logo.jpg',
        ];

        foreach ($urls as $logoUrl) {
            $data = $this->createBaseData(['provider_logo_url' => $logoUrl]);

            $dto = new ConnectionDto($data);

            $this->assertSame($logoUrl, $dto->getLogoUrl());
        }
    }

    /**
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    private function createBaseData(array $overrides = []): array
    {
        return array_merge([
            'id' => 'conn-123',
            'name' => 'Test Bank',
            'status' => ConnectionDto::STATUS_SUCCESS,
            'provider_logo_url' => 'https://example.com/logo.png',
        ], $overrides);
    }
}
