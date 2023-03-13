<?php

declare(strict_types=1);

namespace NGSOFT\Session;

use NGSOFT\{
    DataStructure\Storage, Traits\StringableObject
};
use Stringable;

class Segment implements Storage, Stringable
{

    use StringableObject;

    public function __construct(
            private string $identifier,
            private array $data = []
    )
    {

    }

    public function __debugInfo(): array
    {
        return [];
    }

}
