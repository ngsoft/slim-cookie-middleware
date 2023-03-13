<?php

declare(strict_types=1);

namespace NGSOFT\Session;

use NGSOFT\{
    Interfaces\Storage, Traits\StorageTrait
};
use TypeError;

class Session implements Storage
{

    use StorageTrait;

    private array $segments = [];

    public function __construct(protected string $identifier, array $data = [])
    {

        $this->import($data);
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


        if (isset($this->segments[$segment]))
        {
            throw new RuntimeException(sprintf('Cannot add segment "%s", a segment already exists with this identifier.', $segment));
        }


        return $this->segments[$segment] = new Segment($segment, $data);
    }

    private function isSegment(string $key): bool
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

}
