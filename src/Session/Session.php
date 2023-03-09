<?php

declare(strict_types=1);

namespace NGSOFT\Session;

class Session implements \NGSOFT\Interfaces\Storage
{

    protected array $data = [];

    public function __construct(
            protected string $id
    )
    {

    }

    public function getId(): string
    {
        return $this->id;
    }

    public function save(): void
    {
        $_SESSION = $this->data;
        session_write_close();
    }

    public function count(): int
    {
        return count($this->data);
    }

    public function clear(): void
    {
        $this->data = [];
    }

    public function getItem(string $key): mixed
    {

    }

    public function hasItem(string $key): bool
    {

    }

    public function key(int $index): ?string
    {

    }

    public function removeItem(string $key): void
    {

    }

    public function setItem(string $key, mixed $value): void
    {

    }

}
