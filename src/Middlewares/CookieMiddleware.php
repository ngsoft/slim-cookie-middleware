<?php

declare(strict_types=1);

namespace NGSOFT\Middlewares;

use Psr\Http\Server\MiddlewareInterface;

class CookieMiddleware implements MiddlewareInterface
{

    public function __construct(
            protected string $path = '/',
            protected bool $enabled = true
    )
    {

    }

    public function process(\Psr\Http\Message\ServerRequestInterface $request, \Psr\Http\Server\RequestHandlerInterface $handler): \Psr\Http\Message\ResponseInterface
    {

    }

}
