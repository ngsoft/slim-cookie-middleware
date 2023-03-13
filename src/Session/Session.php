<?php

declare(strict_types=1);

namespace NGSOFT\Session;

use NGSOFT\{
    Interfaces\Storage, Traits\StorageTrait
};

class Session implements Storage
{

    use StorageTrait;

    public function __construct(protected string $id, array $data = [])
    {
        $this->data = $data;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function toArray(): array
    {
        return $this->data;
    }

}
