<?php

declare(strict_types=1);

namespace NGSOFT\Session;

final class Token implements \Stringable, \JsonSerializable
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

        [$name, $value] = self::generateTokenValues($prefix, $strength);

        return new static($name, $value, $prefix);
    }

    /**
     * Load token with its values
     */
    public static function load(string $name, string $value, string $prefix): static
    {
        return new static($name, $value, $prefix);
    }

    private static function generateTokenValues(string $prefix, int $strength): array
    {
        return [uniqid($prefix), self::generateRandomString($strength)];
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
            private readonly string $value,
            private readonly string $prefix
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

    public function getPrefix(): string
    {
        return $this->prefix;
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

    public function getHtml(): string
    {
        return sprintf(
                '<input type="hidden" name="%s_name" value="%s"><input type="hidden" name="%s_value" value="%s">',
                $this->getPrefix(), $this->getName(), $this->getPrefix(), $this->getMaskedValue()
        );
    }

    public function jsonSerialize(): mixed
    {
        $value = $this->getValue();

        if (substr($value, -2) !== '==')
        {
            $value = $this->getMaskedValue();
        }

        return [$this->getName() => $value];
    }

    public function __toString(): string
    {
        return json_encode($this, JSON_UNESCAPED_SLASHES);
    }

}
