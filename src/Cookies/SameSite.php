<?php

declare(strict_types=1);

namespace NGSOFT\Cookies;

/**
 * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Set-Cookie/SameSite SameSite cookies
 */
enum SameSite: string
{


    /**
     * Cookies are not sent on normal cross-site subrequests (for example to load images or frames into a third party site),
     * but are sent when a user is navigating to the origin site (i.e., when following a link).
     *
     * This is the default cookie value if SameSite has not been explicitly specified in recent browser versions.
     */
    case LAX = 'Lax';

    /**
     * Cookies will be sent in all contexts, i.e. in responses to both first-party and cross-site requests.
     * If SameSite=None is set, the cookie Secure attribute must also be set (or the cookie will be blocked).
     */
    case NONE = 'None';

    /**
     * Cookies will only be sent in a first-party context and not be sent along with requests initiated by third party websites.
     */
    case STRICT = 'Strict';

}
