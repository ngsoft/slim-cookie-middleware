<?php

declare(strict_types=1);

namespace NGSOFT\Middlewares;

use Psr\Http\Server\MiddlewareInterface;

class CookieMiddleware implements MiddlewareInterface
{

    protected array $cookies = [
        'request' => [],
        'response' => [],
    ];

    public function __construct(
            protected string $params = [],
            protected bool $enabled = true
    )
    {

    }

    public function process(\Psr\Http\Message\ServerRequestInterface $request, \Psr\Http\Server\RequestHandlerInterface $handler): \Psr\Http\Message\ResponseInterface
    {
        $request = $request->withAttribute('cookies', $this);

        $response = $handler->handle($request);

        return $response;
    }

}
