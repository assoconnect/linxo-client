<?php

declare(strict_types=1);

namespace AssoConnect\LinxoClient\Tests\Dto;

use AssoConnect\LinxoClient\Dto\UserDto;
use PHPUnit\Framework\TestCase;

class UserDtoTest extends TestCase
{
    public function testConstructorSetsAllProperties(): void
    {
        $creationTimestamp = 1710500000;
        $data = [
            'id' => 'user-123',
            'email' => 'john.doe@example.com',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'creation_date' => $creationTimestamp,
        ];

        $dto = new UserDto($data);

        $this->assertSame('user-123', $dto->getId());
        $this->assertSame('john.doe@example.com', $dto->getEmail());
        $this->assertSame('John', $dto->getFirstname());
        $this->assertSame('Doe', $dto->getLastname());
        $this->assertInstanceOf(\DateTimeImmutable::class, $dto->getCreatedAt());
        $this->assertSame($creationTimestamp, $dto->getCreatedAt()->getTimestamp());
    }

    public function testFirstnameIsNullWhenNotProvided(): void
    {
        $data = $this->createBaseData();
        unset($data['first_name']);

        $dto = new UserDto($data);

        $this->assertNull($dto->getFirstname());
    }

    public function testLastnameIsNullWhenNotProvided(): void
    {
        $data = $this->createBaseData();
        unset($data['last_name']);

        $dto = new UserDto($data);

        $this->assertNull($dto->getLastname());
    }

    public function testBothNamesNullWhenNotProvided(): void
    {
        $data = $this->createBaseData();
        unset($data['first_name'], $data['last_name']);

        $dto = new UserDto($data);

        $this->assertNull($dto->getFirstname());
        $this->assertNull($dto->getLastname());
    }

    public function testCreatedAtParsedFromUnixTimestamp(): void
    {
        $timestamp = 1609459200; // 2021-01-01 00:00:00 UTC
        $data = $this->createBaseData(['creation_date' => $timestamp]);

        $dto = new UserDto($data);

        $this->assertSame('2021-01-01', $dto->getCreatedAt()->format('Y-m-d'));
        $this->assertSame($timestamp, $dto->getCreatedAt()->getTimestamp());
    }

    public function testCreatedAtWithRecentTimestamp(): void
    {
        $timestamp = 1704067200; // 2024-01-01 00:00:00 UTC
        $data = $this->createBaseData(['creation_date' => $timestamp]);

        $dto = new UserDto($data);

        $this->assertSame('2024-01-01', $dto->getCreatedAt()->format('Y-m-d'));
    }

    public function testEmailWithVariousFormats(): void
    {
        $emails = [
            'simple@example.com',
            'user.name@example.com',
            'user+tag@example.com',
            'user@subdomain.example.com',
        ];

        foreach ($emails as $email) {
            $data = $this->createBaseData(['email' => $email]);

            $dto = new UserDto($data);

            $this->assertSame($email, $dto->getEmail());
        }
    }

    /**
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    private function createBaseData(array $overrides = []): array
    {
        return array_merge([
            'id' => 'user-123',
            'email' => 'test@example.com',
            'first_name' => 'Test',
            'last_name' => 'User',
            'creation_date' => 1710500000,
        ], $overrides);
    }
}
