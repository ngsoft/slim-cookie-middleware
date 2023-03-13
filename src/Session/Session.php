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

    public function getSegment(string $segment): Segment
    {

        if (isset($this->segments[$segment]))
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

    public function addSegment(string $segment, array $data = []): Segment
    {
        if ($this->hasItem($segment))
        {
            throw new RuntimeException(sprintf('Cannot add segment "%s", data already exists with this name.', $segment));
        }


        if (isset($this->segments[$segment]))
        {

        }
    }

}
