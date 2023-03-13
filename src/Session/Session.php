<?php

declare(strict_types=1);

namespace NGSOFT\Session;

use NGSOFT\Interfaces\Storage;

class Session implements Storage
{

    public function __construct(protected string $id, protected array $data = [])
    {

    }

    public function getId(): string
    {
        return $this->id;
    }

    public function toArray(): array
    {
        return $this->data;
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
        return $this->data[$key] ?? null;
    }

    public function hasItem(string $key): bool
    {

        return $this->getItem($key) !== null;
    }

    public function key(int $index): ?string
    {
        return array_keys($this->data) [$index] ?? null;
    }

    public function removeItem(string $key): void
    {
        unset($this->data[$key]);
    }

    public function setItem(string $key, mixed $value): void
    {
        $this->data[$key] = $value;
    }

}
