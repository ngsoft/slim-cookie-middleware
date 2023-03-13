<?php

declare(strict_types=1);

namespace NGSOFT\Interfaces;

interface Storage extends \Countable, \ArrayAccess
{

    /**
     * When passed a key name, checks if it exists in the storage.
     */
    public function hasItem(string $key): bool;

    /**
     * When passed a key name, will return that key's value.
     */
    public function getItem(string $key): mixed;

    /**
     * When passed a key name and value, will add that key to the storage, or update that key's
     */
    public function setItem(string $key, mixed $value): void;

    /**
     * When passed a key name, will remove that key from the storage.
     */
    public function removeItem(string $key): void;

    /**
     * Clears the Storage.
     */
    public function clear(): void;

    /**
     * When passed a number n, returns the name of the nth key in a given Storage object.
     */
    public function key(int $index): ?string;
}
