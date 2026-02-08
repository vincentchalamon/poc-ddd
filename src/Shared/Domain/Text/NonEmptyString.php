<?php

declare(strict_types=1);

namespace App\Shared\Domain\Text;

use App\Shared\Domain\Exception\NonEmptyStringException;
use Symfony\Component\String\UnicodeString;

use function Symfony\Component\String\u;

/**
 * ValueObject for string encapsulation.
 *
 * @throws NonEmptyStringException When the string contains leading or trailing whitespace, or is shorter than 1 character
 */
final readonly class NonEmptyString
{
    public const int MINIMUM_LENGTH = 1;

    private UnicodeString $text;

    public function __construct(
        string $text,
    ) {
        if (!u($text)->trim()->equalsTo($text)) {
            throw NonEmptyStringException::textMustBeTrimmed($text);
        }

        if (u($text)->length() < self::MINIMUM_LENGTH) {
            throw NonEmptyStringException::textTooShort($text, self::MINIMUM_LENGTH);
        }

        $this->text = u($text);
    }

    public function text(): string
    {
        return $this->text->__toString();
    }

    public function length(): int
    {
        return $this->text->length();
    }
}
