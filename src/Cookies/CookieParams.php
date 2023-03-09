<?php

declare(strict_types=1);

namespace NGSOFT\Cookies;

class CookieParams
{

    public string $path = '/';
    public int $expires = 0;
    public int $ttl = 0;
    public string $domain = '';
    public bool $secure = true;
    public bool $httponly = false;
    public SameSite $samesite = SameSite::Lax;

}
