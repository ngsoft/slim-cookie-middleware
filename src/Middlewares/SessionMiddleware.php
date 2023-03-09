<?php

declare(strict_types=1);

namespace NGSOFT\Middlewares;

use NGSOFT\{
    Cookies\Cookie, Cookies\CookieParams, Cookies\SameSite, Session\Session
};
use Psr\Http\{
    Message\ResponseInterface, Message\ServerRequestInterface, Server\MiddlewareInterface, Server\RequestHandlerInterface
};

/**
 * @link https://discourse.laminas.dev/t/rfc-php-session-and-psr-7/294
 */
class SessionMiddleware implements MiddlewareInterface
{

    public const SESSION_ATTRIBUTE = 'session';

    protected function generateSessionId(): string
    {
        return bin2hex(random_bytes(16));
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {

        switch (session_status())
        {
            case PHP_SESSION_DISABLED:
                return $handler->handle($request);
            case PHP_SESSION_ACTIVE:
                session_abort();
        }


        $session_name = session_name();
        $cookies = $request->getCookieParams();
        $id = $cookies[$session_name] ?? $this->generateSessionId();

        $session = new Session($id);

        $request = $request->withAttribute(self::SESSION_ATTRIBUTE, $session);

        $response = $handler->handle($request);

        $this->session->save();

        if ( ! isset($cookies[$session_name]))
        {
            $cookie = new Cookie($session_name, $id, new CookieParams(secure: true, httponly: true, samesite: SameSite::STRICT));
            $response = $response->withAddedHeader('Set-Cookie', $cookie->getHeaderLine());
        }


        return $response;
    }

}
