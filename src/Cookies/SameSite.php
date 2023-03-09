<?php

declare(strict_types=1);

namespace NGSOFT\Cookies;

enum SameSite: string
{

    case STRICT = 'Strict';
    case LAX = 'Lax';
    case NONE = 'None';

}
