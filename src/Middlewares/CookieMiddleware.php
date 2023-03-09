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
        'response' => [],
        'request' => [],
    ];

    public function __construct(
            protected CookieParams $params = new CookieParams(),
            protected bool $enabled = true
    )
    {

    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /**
         * Register middleware as attribute
         */
        $request = $request->withAttribute('cookies', $this);

        $reqParams = new CookieParams();

        foreach ($request->getCookieParams() as $name => $value)
        {
            if ( ! in_array($name, self::IGNORE_NAMES))
            {
                $this->cookies['request'][$name] = $this->createCookie($name, $value, $reqParams);
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
    public function createCookie(string $name, int|float|bool|string $value, CookieParams $params = null): Cookie
    {
        return new Cookie($name, $value, $params ?? $this->params);
    }

    /**
     * Adds a cookie to the response
     */
    public function setCookie(string $name, int|float|bool|string $value, CookieParams $params = null): void
    {
        $this->addCookie($this->createCookie($name, $value, $params));
    }

    /**
     * Adds a cookie instance to the response
     */
    public function addCookie(Cookie $cookie): void
    {
        $this->cookies['response'] [$cookie->getName()] = $cookie;
    }

    /**
     * Set a cookie to be deleted
     */
    public function removeCookie(string $name): void
    {
        $this->setCookie($name, 'null', $this->params->withExpires(-1));
    }

    /**
     * Get a cookie value by name
     */
    public function getCookie(string $name, mixed $defaultValue = null): mixed
    {

        foreach ($this->cookies as $repository)
        {
            if (isset($repository[$name]))
            {
                return $repository[$name]->getValue();
            }
        }

        return $defaultValue;
    }

}
