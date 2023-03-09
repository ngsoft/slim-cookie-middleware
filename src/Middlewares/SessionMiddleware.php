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

        $cookies = $request->getCookieParams();
        $id = $cookies[session_name()] ?? $this->generateSessionId();

        session_id($id);
        session_start(['use_cookies' => false, 'use_only_cookies' => true]);

        $response = $handler->handle(
                $request->withAttribute(
                        self::SESSION_ATTRIBUTE,
                        $session = new Session($id, $_SESSION)
                )
        );

        $_SESSION = $session->toArray();
        session_write_close();

        if ( ! isset($cookies[session_name()]))
        {
            $cookie = new Cookie(
                    session_name(), $id,
                    new CookieParams(secure: true, httponly: true, samesite: SameSite::STRICT)
            );
            $response = $response->withAddedHeader('Set-Cookie', $cookie->getHeaderLine());
        }


        return $response;
    }

}
