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

    public function getSegment(string $segment)
    {

    }

}
