<?php

declare(strict_types=1);

namespace NGSOFT\Traits;

trait StorageTrait
{

    public function __construct(protected string $identifier, private array $data = [])
    {
        $this->data = $data;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function toArray(): array
    {
        return $this->data;
    }

    public function __debugInfo(): array
    {
        return [];
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

    public function count(): int
    {
        return count($this->data);
    }

    /** {@inheritdoc} */
    public function clear(): void
    {
        $this->data = [];
    }

    /** {@inheritdoc} */
    public function getItem(string $key, mixed $defaultValue = null): mixed
    {

        if ( ! $this->hasItem($key))
        {
            if ($this->checkValue($value = value($defaultValue)))
            {
                $this->setItem($key, $value);
            }

            return $value;
        }


        return $this->data[$key];
    }

    /** {@inheritdoc} */
    public function hasItem(string $key): bool
    {
        return array_key_exists($key, $this->data);
    }

    /** {@inheritdoc} */
    public function key(int $index): ?string
    {
        return array_keys($this->data) [$index] ?? null;
    }

    /** {@inheritdoc} */
    public function removeItem(string $key): void
    {
        unset($this->data[$key]);
    }

    /** {@inheritdoc} */
    public function setItem(string $key, mixed $value): void
    {
        if ( ! $this->checkValue($value))
        {
            throw new InvalidArgumentException('$value is not of int|float|bool|string|array type.');
        }


        $this->data[$key] = $value;
    }

    private function checkValue(mixed $value): bool
    {
        return is_scalar($value) || is_array($value);
    }

}
