<?php

declare(strict_types=1);

namespace NGSOFT\Cookies;

use InvalidArgumentException,
    RuntimeException,
    Stringable;
use const NGSOFT\Tools\DAY;

class Cookie implements Stringable
{

    protected const INVALID_NAME_REGEX = '/[=,; \t\r\n\013\014]/';

    protected string $name;
    protected string $value;

    public function __construct(
            string $name,
            int|float|bool|string $value,
            protected CookieAttributes $attributes = new CookieAttributes()
    )
    {
        $this->setName($name);
        $this->setValue($value);
    }

    /**
     * Get a new cookie with defined params
     */
    public function withAttributes(CookieAttributes $params): static
    {
        $clone = clone $this;
        $clone->params = $params;
        return $clone;
    }

    public function withKeyValuePair(string $name, int|float|bool|string $value): static
    {
        return $this->withName($name)->withValue($value);
    }

    /**
     * Get a new cookie with provided name
     */
    public function withName(string $name): static
    {

        $clone = clone $this;
        $clone->setName($name);
        return $clone;
    }

    /**
     * Get a new cookie with provided value
     */
    public function withValue(int|float|bool|string $value): static
    {
        $clone = clone $this;
        $clone->setValue($value);
        return $clone;
    }

    protected function setName(string $name)
    {
        if (empty($name) || preg_match(self::INVALID_NAME_REGEX, $name))
        {
            throw new InvalidArgumentException(sprintf('Invalid cookie name "%s".', $name));
        }

        $this->name = $name;
        return $this;
    }

    protected function setValue(int|float|bool|string $value)
    {
        $this->value = is_string($value) ? $value : json_encode($value);
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getValue(): mixed
    {

        $value = $this->value;

        if (preg_match('/^([\d\.]+|true|false|null)$/', $value))
        {
            $value = json_decode($value);
        }

        return $value;
    }

    public function getAttributes(): CookieParams
    {
        return $this->attributes;
    }

    public function getHeaderLine(): string
    {
        $params = $this->attributes;

        $now = time();

        $expires = $params->expiresAfter * DAY;

        $ttl = 0;

        if ($expires !== 0)
        {
            $expires += $now;
            $ttl = $expires - $now;
        }



        if ($params->samesite === SameSite::NONE && $params->secure === false)
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

        if ($ttl > 0)
        {
            $result .= sprintf('; Expires=%s; Max-Age=%u', gmdate('D, d M Y H:i:s \G\M\T', $expires), $ttl);
        }
        elseif ($ttl < 0)
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
