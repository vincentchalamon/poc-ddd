<?php

declare(strict_types=1);

namespace App\Shared\Domain\Exception;

final class FloatValueException extends \DomainException
{
    public static function emptyValue(): self
    {
        return new self('Value must not be empty.');
    }

    public static function invalidNumber(int|float|string $value): self
    {
        return new self(\sprintf('Value must not be a valid number, got %s.', $value));
    }

    public static function tooBigNumber(int|float|string $value): self
    {
        return new self(\sprintf('Value must not exceed or equal to 10 million, got %s.', $value));
    }

    public static function divisionByZeroIsNotPermitted(): self
    {
        return new self('Divide by zero is not permitted.');
    }

    private function __construct(string $message)
    {
        parent::__construct($message);
    }
}
