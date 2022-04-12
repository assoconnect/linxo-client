<?php

declare(strict_types=1);

namespace AssoConnect\LinxoClient\Dto;

class UserDto
{
    private string $id;
    private string $email;
    private ?string $firstname;
    private ?string $lastname;
    private \DateTimeImmutable $createdAt;
    private array $data;

    public function __construct(array $data)
    {
        $this->id = $data['id'];
        $this->email = $data['email'];
        $this->firstname = $data['first_name'] ?? null;
        $this->lastname = $data['last_name'] ?? null;
        $this->createdAt = new \DateTimeImmutable('@' . $data['creation_date']);
        $this->data = $data;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    /** @codeCoverageIgnore */
    public function getData(): array
    {
        return $this->data;
    }
}
