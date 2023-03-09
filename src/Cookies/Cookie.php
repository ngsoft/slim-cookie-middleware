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

        [$expire, $now] = $params;

        if ($params->samesite === SameSite::None && $params->secure === false)
        {
            throw new RuntimeException('SameSite cannot be "None" when secure is false.');
        }


        $result = sprintf('%s=%s', urlencode($this->name), urldecode($this->value));

        if ( ! empty($params->domain))
        {
            $result .= sprintf('; Domain=%s', $params->domain);
        }

        if ( ! empty($params->path))
        {
            $result .= sprintf('; Path=%s', $params->path);
        }






        return $result;
    }

    public function __toString(): string
    {
        return $this->getHeaderLine();
    }

}



