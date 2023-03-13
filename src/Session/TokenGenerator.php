<?php

declare(strict_types=1);

namespace NGSOFT\Session;

class TokenGenerator
{

    public function __construct(
            private int $strength = 16
    )
    {

    }

}
