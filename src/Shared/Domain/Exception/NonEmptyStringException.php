<?php

declare(strict_types=1);

namespace App\Shared\Domain\Exception;

use function Symfony\Component\String\u;

final class NonEmptyStringException extends \DomainException
{
    public static function textMustBeTrimmed(string $text): self
    {
        // Uses brackets to visually show the whitespace
        return new self(\sprintf('The text contains spaces in prefixes or suffixes and must be trimmed. Text: "[%s]".', $text));
    }

    public static function textTooShort(string $text, int $allowedLength): self
    {
        return new self(\sprintf('The text should be longer than "%s" characters but is "%s" characters long.', $allowedLength, u($text)->length()));
    }

    private function __construct(string $message)
    {
        parent::__construct($message);
    }
}
