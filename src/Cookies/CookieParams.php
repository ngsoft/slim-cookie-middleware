<?php

declare(strict_types=1);

namespace NGSOFT\Cookies;

class CookieParams
{

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

    public function withExpiresAfter(int $days)
    {
        return $this->getClone('expiresAfter', $days);
    }

    public function withPath(string $path)
    {
        return $this->getClone('path', $path);
    }

    public function withDomain(string $domain)
    {
        return $this->getClone('domain', $domain);
    }

    public function withSecure(bool $secure)
    {
        return $this->getClone('secure', $secure);
    }

    public function withHttponly(bool $httponly)
    {
        return $this->getClone('httponly', $httponly);
    }

    public function withSamesite(SameSite $samesite)
    {
        return $this->getClone('samesite', $samesite);
    }

}
