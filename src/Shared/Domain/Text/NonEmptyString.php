<?php

declare(strict_types=1);

namespace App\Shared\Domain\Text;

use App\Shared\Domain\Exception\NonEmptyStringException;
use Doctrine\Common\Comparable;
use Symfony\Component\String\UnicodeString;

use function Symfony\Component\String\u;

/**
 * ValueObject for string encapsulation.
 *
 * @throws NonEmptyStringException When the string contains leading or trailing whitespace, or is shorter than 1 character
 */
readonly class NonEmptyString implements \Stringable, Comparable
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

    #[\Override]
    public function __toString(): string
    {
        return $this->text();
    }

    #[\Override]
    public function compareTo($other): int
    {
        $compare = strcmp((string) $this, (string) $other);

        if ($compare < 0) {
            return -1;
        }

        if ($compare > 0) {
            return 1;
        }

        return 0;
    }

    public function text(): string
    {
        return (string) $this->text;
    }

    public function length(): int
    {
        return $this->text->length();
    }
}
