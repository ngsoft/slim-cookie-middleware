<?php

declare(strict_types=1);

namespace NGSOFT\Session;

use NGSOFT\{
    Interfaces\Storage, Traits\StorageTrait
};

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


        if (array_key_exists($segment, $this->data))
        {

            if (is_array($value = $this->data[$name]))
            {

            }
        }
    }

}
