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
    protected const IGNORE_NAMES = [
        'PHPSESSID'
    ];

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

        foreach ($request->getCookieParams() as $name => $value)
        {
            if ( ! in_array($name, self::IGNORE_NAMES))
            {
                $this->cookies['request'][$name] = $this->createCookie($name, $value);
            }
        }

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

    /**
     * Create a cookie
     */
    public function createCookie(string $name, string $value, CookieParams $params = null): Cookie
    {
        return new Cookie($name, $value, $params ?? $this->params);
    }

    /**
     * Adds a cookie to the response
     */
    public function setCookie(string $name, string $value, CookieParams $params = null): void
    {
        $this->cookies['response'] [$name] = $this->createCookie($name, $value, $params);
    }

    public function removeCookie(string $name): void
    {
        $this->setCookie($name, 'deleted', $this->params->withExpires(-1));
    }

}
