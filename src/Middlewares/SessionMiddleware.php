<?php

declare(strict_types=1);

namespace NGSOFT\Middlewares;

use Psr\Http\{
    Message\ResponseInterface, Message\ServerRequestInterface, Server\MiddlewareInterface, Server\RequestHandlerInterface
};

/**
 * @link https://discourse.laminas.dev/t/rfc-php-session-and-psr-7/294
 */
class SessionMiddleware implements MiddlewareInterface
{

    public const SESSION_ATTRIBUTE = 'session';

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {

        /**
         * Creates session
         */
        if (php_sapi_name() !== "cli")
        {
            session_set_cookie_params([
                'secure' => true,
                'httponly' => true,
                'samesite' => 'Strict',
            ]);
            session_start();
        }
    }

}
