<?php

declare(strict_types=1);

namespace NGSOFT\Session;

use NGSOFT\{
    Interfaces\Storage, Traits\StorageTrait
};

final class Segment implements Storage
{

    use StorageTrait;

    public function getItem(string $key, mixed $defaultValue = null): mixed
    {

    }

    public function setItem(string $key, mixed $value): void
    {

    }

}
