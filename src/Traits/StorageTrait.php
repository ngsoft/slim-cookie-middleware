<?php

declare(strict_types=1);

namespace NGSOFT\Traits;

trait StorageTrait
{

    protected string $identifier = '';
    private array $data = [];

    public function __debugInfo(): array
    {
        return [];
    }

    /**
     * Gets storage identifier
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * Exports storage to array
     */
    public function toArray(): array
    {
        return $this->data;
    }

    /**
     * Clears the Storage.
     */
    public function clear(): void
    {
        $this->data = [];
    }

    /**
     * When passed a number n, returns the name of the nth key in a given Storage object.
     */
    public function key(int $index): ?string
    {
        return array_keys($this->data) [$index] ?? null;
    }

    /**
     * Returns the storage length
     */
    public function count(): int
    {
        return count($this->data);
    }

    abstract public function setItem(string $key, mixed $value): void;

    abstract public function getItem(string $key, mixed $defaultValue = null): mixed;

    abstract public function removeItem(string $key): void;

    /**
     * When passed a key name, checks if it exists in the storage.
     */
    public function hasItem(string $key): bool
    {
        return array_key_exists($key, $this->data);
    }

    public function offsetExists(mixed $offset): bool
    {
        return $this->hasItem($offset);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->getItem($offset);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->setItem($offset, $value);
    }

    public function offsetUnset(mixed $offset): void
    {
        $this->removeItem($offset);
    }

    private function checkValue(mixed $value): bool
    {
        return is_scalar($value) || is_array($value);
    }

    private function assertValidValue(mixed $value): void
    {
        if ( ! $this->checkValue($value))
        {
            throw new InvalidArgumentException(sprintf('$value of type %s is not of int|float|bool|string|array type.', get_debug_type($value)));
        }
    }

}
