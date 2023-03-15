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

    public function __construct(
            protected ?CookieMiddleware $cookieMiddleware = null
    )
    {

    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {


        $random = Token::generateRandomString();

        if (PHP_SESSION_DISABLED !== session_status())
        {
            /** @var CookieMiddleware $cookieMiddleware */
            $cookieMiddleware = $request->getAttribute(CookieMiddleware::COOKIE_ATTRIBUTE) ?? $this->cookieMiddleware ??= new CookieMiddleware();

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
                $this->saveSession($session);
            }

            if (is_null($id))
            {

                $cookieMiddleware->addCookie(
                        new Cookie(session_name(), $session->getIdentifier(),
                                new CookieParams(
                                        path: '/',
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

    protected function saveSession(Session $session): void
    {
        $this->abortSession();

        try
        {
            @session_id($session->getIdentifier());
            if (@session_start(['use_cookies' => false, 'use_only_cookies' => true]))
            {
                $_SESSION = $session->toArray();
            }
        }
        finally
        {
            @session_write_close();
        }
    }

}
