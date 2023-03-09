<?php

declare(strict_types=1);

namespace NGSOFT\Cookies;

use InvalidArgumentException,
    RuntimeException,
    Stringable;

class Cookie implements Stringable
{

    protected const INVALID_NAME_REGEX = '/[=,; \t\r\n\013\014]/';

    public function __construct(
            public string $name,
            public string $value,
            public CookieParams $params = new CookieParams()
    )
    {

        if (empty($name) || preg_match(self::INVALID_NAME_REGEX, $name))
        {
            throw new InvalidArgumentException(sprintf('Invalid cookie name "%s".', $name));
        }
    }

    public function getHeaderLine(): string
    {
        $params = $this->params;

        $now = time();

        $expires = $params->expires;

        $ttl = 0;

        if ($expires !== 0)
        {
            $expires += $now;
        }

        if ($expires > $now)
        {
            $ttl = $expires - $now;
        }

        if ($params->samesite === SameSite::None && $params->secure === false)
        {
            throw new RuntimeException('SameSite cannot be "None" when secure is false.');
        }


        $result = sprintf('%s=%s; SameSite=%s', urlencode($this->name), urldecode($this->value), $params->samesite->value);

        if ( ! empty($params->path))
        {
            $result .= sprintf('; Path=%s', $params->path);
        }
        if ( ! empty($params->domain))
        {
            $result .= sprintf('; Domain=%s', $params->domain);
        }

        if ($expires > $now)
        {
            $result .= sprintf('; Expires=%s; Max-Age=%u', gmdate('D, d M Y H:i:s \G\M\T', $expires), $ttl);
        }
        else
        {
            $result .= sprintf('; Expires=%s; Max-Age=0', gmdate('D, d M Y H:i:s \G\M\T', $expires));
        }

        if ($params->secure)
        {
            $result .= '; Secure';
        }

        if ($params->httponly)
        {
            $result .= '; HttpOnly';
        }

        return $result;
    }

    public function __toString(): string
    {
        return $this->getHeaderLine();
    }

}
