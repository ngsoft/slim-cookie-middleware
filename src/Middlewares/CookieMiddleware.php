<?php

declare(strict_types=1);

namespace NGSOFT\Middlewares;

use NGSOFT\Cookies\CookieParams;
use Psr\Http\{
    Message\ResponseInterface, Message\ServerRequestInterface, Server\MiddlewareInterface, Server\RequestHandlerInterface
};

class CookieMiddleware implements MiddlewareInterface
{

    public const VERSION = '1.0.0';

    protected array $cookies = [
        'request' => [],
        'response' => [],
    ];

    public function __construct(
            protected CookieParams $params = new CookieParams(),
            protected bool $enabled = true
    )
    {

    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $request = $request->withAttribute('cookies', $this);

        $response = $handler->handle($request);

        return $response;
    }

}
