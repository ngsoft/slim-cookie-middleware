<?php

declare(strict_types=1);

namespace NGSOFT\Session;

use NGSOFT\{
    DataStructure\Storage, Traits\StringableObject
};
use Stringable,
    ValueError;
use function get_debug_type,
             NGSOFT\Tools\count_value,
             value;

class Segment implements Storage, Stringable
{

    use StringableObject;

    protected array $data;
    protected array $segments;

    public function __construct(
            protected string $identifier,
            array $data = []
    )
    {
        $this->import($data);
    }

    public function __debugInfo(): array
    {
        return [];
    }

    protected function import(array $data)
    {
        $this->clear();
    }

    protected function checkValue(mixed $value): bool
    {
        return is_scalar($value) || is_array($value);
    }

    protected function assertValidValue(mixed $value): void
    {
        if ( ! $this->checkValue($value))
        {
            throw new ValueError(sprintf('$value of type %s is not of int|float|bool|string|array type.', get_debug_type($value)));
        }
    }

    /**
     * Clears the Storage.
     */
    public function clear(): void
    {
        $this->data = [];
        $this->segments = [];
    }

    /**
     * Count the storage if no value, else count the number of occurences of the value
     */
    public function count(mixed $value = null): int
    {
        if (is_null($value))
        {
            return count($this->data);
        }

        if ($value instanceof Segment)
        {
            $value = $value->toArray();
        }

        return count_value($value->toArray(), $this->data);
    }

    /**
     * When passed a key name, will return that key's value.
     */
    public function getItem(string $key, mixed $defaultValue = null): mixed
    {

        if ($this->hasItem($key))
        {
            if (is_array($this->data[$key]))
            {
                return $this->segments[$key] ??= new Segment($key, $this->data[$key]);
            }

            return $this->data[$key];
        }

        if ($this->checkValue($value = value($defaultValue)))
        {
            $this->setItem($key, $value);
            return $this->getItem($key);
        }

        return $value;
    }

    /**
     * When passed a key name, checks if it exists in the storage.
     */
    public function hasItem(string $key): bool
    {
        return array_key_exists($key, $this->data);
    }

    /**
     * When passed a number n, returns the name of the nth key in a given Storage object.
     * if n is negative value, returns the name of the nth key from the end of the storage object.
     */
    public function key(int $index): ?string
    {
        if ($index < 0)
        {
            $index += $this->count();
        }

        return array_keys($this->data) [$index] ?? null;
    }

    /**
     * When passed a key name, will remove that key from the storage.
     */
    public function removeItem(string $key): void
    {
        unset($this->data[$key], $this->segments[$key]);
    }

    /**
     * When passed a key name and value, will add that key to the storage, or update that key's
     */
    public function setItem(string $key, mixed $value): void
    {
        $this->assertValidValue($value = value($value));
        $this->removeItem($key);
        $this->data[$key] = $value;
    }

}
