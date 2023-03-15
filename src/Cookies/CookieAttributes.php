<?php

declare(strict_types=1);

namespace NGSOFT\Cookies;

class CookieAttributes
{

    /**
     *
     * @param int $expiresAfter Days before expiration
     * @param string $path cookie path
     * @param string $domain cookie domain
     * @param bool $secure https
     * @param bool $httponly Javascript does realy need to read this cookie ?
     * @param SameSite $samesite Samesite attribute
     */
    public function __construct(
            public int $expiresAfter = 0,
            public string $path = '',
            public string $domain = '',
            public bool $secure = true,
            public bool $httponly = false,
            public SameSite $samesite = SameSite::LAX
    )
    {

    }

    protected function getClone(string $prop, mixed $value): static
    {
        $clone = clone $this;
        $clone->{$prop} = $value;
        return $clone;
    }

    /**
     * Get a new attribute with provided expiration in days
     * if expiration is negative, cookie will be deleted
     */
    public function withExpiresAfter(int $days)
    {
        return $this->getClone('expiresAfter', $days);
    }

    /**
     * Get a new attribute with provided path
     */
    public function withPath(string $path)
    {
        return $this->getClone('path', $path);
    }

    /**
     * Get a new attribute with provided domain
     */
    public function withDomain(string $domain)
    {
        return $this->getClone('domain', $domain);
    }

    /**
     * Get a new attribute with provided param
     */
    public function withSecure(bool $secure)
    {
        return $this->getClone('secure', $secure);
    }

    /**
     * Get a new attribute with provided param
     */
    public function withHttponly(bool $httponly)
    {
        return $this->getClone('httponly', $httponly);
    }

    /**
     * Get a new attribute with provided param
     */
    public function withSamesite(SameSite $samesite)
    {
        return $this->getClone('samesite', $samesite);
    }

}
