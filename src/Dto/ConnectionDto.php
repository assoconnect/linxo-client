<?php

declare(strict_types=1);

namespace AssoConnect\LinxoClient\Dto;

class ConnectionDto
{
    private string $id;
    private string $name;
    private string $status;
    private string $logoUrl;

    public const STATUS_RUNNING = 'RUNNING';
    public const STATUS_SUCCESS = 'SUCCESS';
    public const STATUS_PARTIAL_SUCCESS = 'PARTIAL_SUCCESS';
    public const STATUS_FAILED = 'FAILED';
    public const STATUS_CLOSED = 'CLOSED';
    public const STATUS_NONE = 'NONE';

    public function __construct(array $data)
    {
        $this->id = $data['id'];
        $this->name = $data['name'];
        $this->status = $data['status'];
        $this->logoUrl = $data['logo_url'];
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getLogoUrl(): string
    {
        return $this->logoUrl;
    }
}
