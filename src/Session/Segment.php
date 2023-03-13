<?php

declare(strict_types=1);

namespace NGSOFT\Session;

use NGSOFT\{
    Interfaces\Storage, Traits\StorageTrait
};

final class Segment implements Storage
{

    use StorageTrait;

    /**
     * When passed a key name, will return that key's value.
     */
    public function getItem(string $key, mixed $defaultValue = null): mixed
    {
        if ( ! $this->hasItem($key))
        {
            if ($defaultValue instanceof \Closure || $this->checkValue($defaultValue))
            {

                $this->setItem($key, $defaultValue);
            }
            else
            { return $defaultValue; }
        }


        return $this->data[$key];
    }

    /**
     * When passed a key name and value, will add that key to the storage, or update that key's
     */
    public function setItem(string $key, mixed $value): void
    {
        $this->assertValidValue($value = value($value));
        $this->data[$key] = $value;
    }

    /**
     * When passed a key name, will remove that key from the storage.
     */
    public function removeItem(string $key): void
    {

        unset($this->data[$key]);
    }

}
