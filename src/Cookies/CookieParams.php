<?php

declare(strict_types=1);

namespace NGSOFT\Cookies;

class CookieParams
{

    public function __construct(
            public int $expires = 0,
            public string $path = '',
            public string $domain = '',
            public bool $secure = true,
            public bool $httponly = false,
            public SameSite $samesite = SameSite::Lax
    )
    {

    }

}
