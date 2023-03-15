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

    use \NGSOFT\Traits\ObjectLock;

    public const VERSION = '1.1.0';
    public const COOKIE_ATTRIBUTE = 'cookies';

    protected array $cookies = [
        'response' => [],
        'request' => [],
    ];

    public function __construct(
            protected CookieParams $params = new CookieParams()
    )
    {

    }

    /**
     * Participant in processing a server request and response.
     *
     * An HTTP middleware component participates in processing an HTTP message:
     * by acting on the request, generating the response, or forwarding the
     * request to a subsequent middleware and possibly acting on its response.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /**
         * Register middleware as attribute
         */
        $request = $request->withAttribute(static::COOKIE_ATTRIBUTE, $this);

        $reqParams = $this->params;

        foreach ($request->getCookieParams() as $name => $value)
        {
            $this->cookies['request'][$name] = $this->createCookie($name, $value, $reqParams);
        }

        return $this->createResponse($handler->handle($request));
    }

    /**
     * Adds cookies to the response
     */
    protected function createResponse(ResponseInterface $response): ResponseInterface
    {

        if ( ! $this->isLocked())
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
    public function setCookie(string $name, int|float|bool|string $value, CookieParams $params = null): Cookie
    {
        return $this->addCookie($this->createCookie($name, $value, $params));
    }

    /**
     * Adds a cookie instance to the response
     */
    public function addCookie(Cookie $cookie): Cookie
    {
        return $this->cookies['response'] [$cookie->getName()] = $cookie;
    }

    /**
     * Set a cookie to be deleted
     */
    public function removeCookie(string $name): Cookie
    {
        return $this->setCookie($name, 'null', $this->params->withExpiresAfter(-1));
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

        return value($defaultValue);
    }

}
