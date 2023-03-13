<?php

declare(strict_types=1);

namespace NGSOFT\Session;

use Closure;
use NGSOFT\{
    Interfaces\Storage, Traits\StorageTrait
};
use RuntimeException;
use function value;

class Session implements Storage
{

    use StorageTrait;

    private array $segments = [];

    public function __construct(string $identifier, array $data = [])
    {
        $this->identifier = $identifier;
        $this->import($data);
    }

    public function offsetExists(mixed $offset): bool
    {
        return $this->hasSegment($offset) || $this->hasItem($offset);
    }

    public function offsetGet(mixed $offset): mixed
    {

        if ($this->hasSegment($offset))
        {
            return $this->getSegment($offset);
        }

        return $this->getItem($offset);
    }

    /**
     * When passed a key name, will return that key's value.
     */
    public function getItem(string $key, mixed $defaultValue = null): mixed
    {
        $this->assertSegment($key);

        if ( ! $this->hasItem($key))
        {
            if ($defaultValue instanceof Closure || $this->checkValue($defaultValue))
            {

                $this->setItem($key, $defaultValue);
            }
            else
            {
                return $defaultValue;
            }
        }


        return $this->data[$key];
    }

    /**
     * When passed a key name and value, will add that key to the storage, or update that key's
     */
    public function setItem(string $key, mixed $value): void
    {
        $this->assertSegment($key);
        $this->assertValidValue($value = value($value));
        $this->data[$key] = $value;
    }

    /**
     * When passed a key name, will remove that key from the storage.
     */
    public function removeItem(string $key): void
    {
        $this->assertSegment($key);
        unset($this->data[$key]);
    }

    /**
     * Get session segment
     */
    public function getSegment(string $segment): Segment
    {

        if ($this->isSegment($segment))
        {
            return $this->segments[$segment];
        }


        if (array_key_exists($segment, $this->data) && is_array($value = $this->data[$segment]))
        {
            unset($this->data[$segment]);
            return $this->segments[$segment] = new Segment($segment, $value);
        }

        throw new RuntimeException(sprintf('Segment "%s" does not exists.'));
    }

    /**
     * Adds a segment to the session and returns it
     */
    public function addSegment(string $segment, array $data = []): Segment
    {
        if ($this->hasItem($segment))
        {
            throw new RuntimeException(sprintf('Cannot add segment "%s", data already exists with this identifier.', $segment));
        }


        if ($this->hasSegment($segment))
        {
            throw new RuntimeException(sprintf('Cannot add segment "%s", a segment already exists with this identifier.', $segment));
        }


        return $this->segments[$segment] = new Segment($segment, $data);
    }

    /**
     * Checks if segment exists
     */
    public function hasSegment(string $key): bool
    {
        return isset($this->segments[$key]);
    }

    private function import(array $data)
    {
        $this->data = [];
        $this->segments = [];

        foreach ($data as $key => $value)
        {

            $value = value($value);
            if (is_scalar($value))
            {
                $this->setItem($key, $value);
            }
            elseif (is_array($value))
            {
                $this->addSegment($key, $value);
            }
        }
    }

    private function assertSegment(string $key): void
    {

        if ($this->hasSegment($key))
        {

            throw new RuntimeException(sprintf('Trying to access data by key "%s", but a segment exists with this identifier.'));
        }
    }

}
