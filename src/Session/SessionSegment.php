<?php

declare(strict_types=1);

namespace NGSOFT\Session;

use NGSOFT\{
    DataStructure\Range, DataStructure\ReversibleIterator, DataStructure\Sort, DataStructure\Storage, Traits\ReversibleIteratorTrait, Traits\StringableObject
};
use Stringable,
    ValueError;
use function get_debug_type,
             NGSOFT\Tools\count_value,
             value;

/**
 *
 */
class SessionSegment implements Storage, Stringable, ReversibleIterator
{

    use StringableObject,
        ReversibleIteratorTrait;

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

        foreach ($data as $key => $value)
        {
            if ($this->checkValue($value = value($value)))
            {
                $this->setItem($key, $value);
            }
        }
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

    protected function createSegment($key): self
    {
        // modifications in the segment will also be made in the parent array
        $segment = new SessionSegment($key);
        $segment->data = &$this->data[$key];
        return $segment;
    }

    /**
     * Get Storage identifier
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * Extract the storage to an array
     */
    public function toArray(): array
    {
        return $this->data;
    }

    public function jsonSerialize(): mixed
    {

        return $this->toArray();
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

        if ($value instanceof SessionSegment)
        {
            $value = $value->toArray();
        }

        if ( ! $this->checkValue($value))
        {
            return 0;
        }

        return count_value($value, $this->data);
    }

    /**
     * When passed a key name, will return that key's value.
     */
    public function getItem(string $key, mixed $defaultValue = null): mixed
    {

        if ($this->hasItem($key))
        {
            if (is_array($this->data[$key]) && ! array_is_list($this->data[$key]))
            {
                return $this->segments[$key] ??= $this->createSegment($key);
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
        try
        {
            $value = value($value);

            // Session extends Segment so to not inject session into session or segment we do this
            if (is_object($value) && get_class($value) === self::class)
            {
                if ($value->data !== $this->data)
                {
                    $value = $value->data;
                }
            }

            $this->assertValidValue($value);
            // we check data recursively
            if (is_array($value) && ! array_is_list($value))
            {
                // this segment will never be used except for this
                $segment = new SessionSegment($key, $value);
                $this->data[$key] = $segment->data;
                return;
            }

            $this->removeItem($key);
            $this->data[$key] = $value;
        }
        finally
        {
            ksort($this->data);
        }
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

    /**
     * Iterates entries from the session segment
     */
    public function entries(Sort $sort = Sort::ASC): iterable
    {

        foreach (Range::of($this)->entries($sort) as $index)
        {
            if ($key = $this->key($index))
            {
                yield $key => $this->getItem($key);
            }
        }
    }

}
