<?php

declare(strict_types=1);

namespace NGSOFT\Session;

final class Token implements \Stringable
{

    public const DEFAULT_PREFIX = 'csrf';
    public const MIN_STRENGTH = 16;

    public static function generateRandomString(int $strength = self::MIN_STRENGTH): string
    {
        return bin2hex(random_bytes(intval(max(self::MIN_STRENGTH, $strength))));
    }

    /**
     * Generate a new Token
     */
    public static function generateToken(string $prefix = self::DEFAULT_PREFIX, int $strength = self::MIN_STRENGTH): static
    {

        $name = uniqid($prefix);
        $value = self::generateRandomString($strength);

        return new static($name, $value);
    }

    private static function maskTokenValue(string $value): string
    {
        $key = random_bytes(strlen($value));
        return base64_encode($key . ($key ^ $value));
    }

    private static function unmaskTokenValue(string $maskedValue): string
    {

        $decoded = base64_decode($maskedValue, true);
        if (false === $decoded)
        {
            return '';
        }

        $length = strlen($decoded) / 2;
        if ( ! is_int($length))
        {
            return '';
        }
        $key = substr($decoded, 0, $length);
        $value = substr($decoded, $length, $length);

        return $key ^ $value;
    }

    public function __construct(
            private readonly string $name,
            private readonly string $value
    )
    {

    }

    public function __debugInfo(): array
    {
        return [$this->getName(), $this->getValue(), $this->getMaskedValue()];
    }

    /**
     * Validates the token
     */
    public function equals(Token $token): bool
    {

        if ($token->name !== $this->name)
        {
            return false;
        }


        if ($token->value === $this->value)
        {
            return true;
        }

        return hash_equals($this->value, self::unmaskTokenValue($token->value));
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getMaskedValue(): string
    {
        return self::maskTokenValue($this->getValue());
    }

    public function getHtml(string $prefix = self::DEFAULT_PREFIX): string
    {
        return sprintf(
                '<input type="hidden" name="%s_name" value="%s"><input type="hidden" name="%s_value" value="%s">',
                $prefix, $this->getName(), $prefix, $this->getMaskedValue()
        );
    }

    public function __toString(): string
    {
        return '';
    }

}
