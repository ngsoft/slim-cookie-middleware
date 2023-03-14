<?php

declare(strict_types=1);

namespace NGSOFT\Middlewares;

use NGSOFT\{
    Cookies\Cookie, Cookies\CookieParams, Cookies\SameSite, Session\Session, Session\Token, Traits\ObjectLock
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

    protected Session $session;

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {

        switch (session_status())
        {
            case PHP_SESSION_DISABLED:
                return $handler->handle($request);
            case PHP_SESSION_ACTIVE:
                session_abort();
        }


        /** @var CookieMiddleware|null $cookieMiddleware */
        $cookieMiddleware = $request->getAttribute(CookieMiddleware::COOKIE_ATTRIBUTE);

        $random = Token::generateRandomString();

        $cookies = $request->getCookieParams();
        $id = $cookies[session_name()] ?? Token::generateRandomString();

        if ($cookieMiddleware instanceof CookieMiddleware)
        {

            if ($id = $cookieMiddleware->getCookie(session_name()))
            {
                $session = new Session($id, $this->loadSession($id));
            }
            else
            {
                $session = new Session($random);
            }


            $response = $handler->handle(
                    $request->withAttribute(
                            static::SESSION_ATTRIBUTE,
                            $session
                    )
            );

            if ( ! $cookieMiddleware->isLocked())
            {
                $this->saveSession($random, $session->toArray());
            }

            if (is_null($id))
            {

                $cookieMiddleware->addCookie(
                        new Cookie(session_name(), $random,
                                new CookieParams(
                                        secure: true,
                                        httponly: true,
                                        samesite: SameSite::STRICT
                                )
                        )
                );
            }
            return $response;
        }

        return $handler->handle($request->withAttribute(self::SESSION_ATTRIBUTE, new Session($random)));
    }

    protected function abortSession(): void
    {
        if (PHP_SESSION_ACTIVE === session_status())
        {
            @session_abort();
        }
    }

    protected function loadSession(string $id): array
    {

        $this->abortSession();

        try
        {

            @session_id($id);
            if (@session_start(['use_cookies' => false, 'use_only_cookies' => true]))
            {
                return $_SESSION;
            }

            return [];
        }
        finally
        {
            $this->abortSession();
        }
    }

    protected function saveSession(string $id, array $data): void
    {
        $this->abortSession();

        try
        {
            @session_id($id);
            if (@session_start(['use_cookies' => false, 'use_only_cookies' => true]))
            {
                $_SESSION = $data;
            }
        }
        finally
        {
            @session_write_close();
        }
    }

}
