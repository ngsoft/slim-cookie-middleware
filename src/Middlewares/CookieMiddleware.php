<?php

declare(strict_types=1);

namespace NGSOFT\Middlewares;

use NGSOFT\{
    Cookies\Cookie, Cookies\CookieAttributes, Cookies\CookieParams, Traits\ObjectLock
};
use Psr\Http\{
    Message\ResponseInterface, Message\ServerRequestInterface, Server\MiddlewareInterface, Server\RequestHandlerInterface
};
use function value;

class CookieMiddleware implements MiddlewareInterface
{

    use ObjectLock;

    public const VERSION = '1.1.0';
    public const COOKIE_ATTRIBUTE = 'cookies';
    public const SESSION_ATTRIBUTE = 'session';

    protected array $cookies = [
        'response' => [],
        'request' => [],
    ];

    public function __construct(
            protected CookieAttributes $params = new CookieAttributes(),
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

    ////////////////////////////   Session Handling   ////////////////////////////

    /**
     * Generates random string for the session id
     */
    protected function generateRandomString(int $strength = 16): string
    {
        return bin2hex(random_bytes(intval(max(16, $strength))));
    }

    /**
     * Session Handling integrated middleware
     */
    protected function processSession(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        if (PHP_SESSION_DISABLED === session_status())
        {
            return $response;
        }
    }

    ////////////////////////////   Cookie Handling   ////////////////////////////

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
