<?php

declare(strict_types=1);

namespace NGSOFT\Middlewares;

use NGSOFT\Cookies\{
    Cookie, CookieParams
};
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

        return $this->createResponse($handler->handle($request));
    }

    protected function createResponse(ResponseInterface $response): ResponseInterface
    {

        if ($this->enabled)
        {

            /** @var Cookie $cookie */
            foreach ($this->cookies['response'] as $cookie)
            {
                $response = $response->withAddedHeader('Set-Cookie', $cookie->getHeaderLine());
            }
        }


        return $response;
    }

}
