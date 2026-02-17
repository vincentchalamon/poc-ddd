<?php

declare(strict_types=1);

namespace App\Shared\Domain\Number;

use App\Shared\Domain\Exception\FloatValueException;
use Doctrine\Common\Comparable;

/**
 * Handles floats through Value Object to prevent bugs in PHP.
 *
 * Note: classes named 'int', 'string', 'float', 'bool', 'true', 'false' and 'null' are forbidden.
 *
 * @see https://bugs.xdebug.org/view.php?id=2151
 * @see https://www.php.net/manual/en/intro.bc.php
 */
readonly class FloatValue implements \Stringable, Comparable
{
    public const int DECIMALS = 10;

    private string $value;

    public function __construct(
        int|float|string $value,
    ) {
        $value = trim((string) $value);
        if ('' === $value) {
            throw FloatValueException::emptyValue();
        }

        if (!is_numeric($value)) {
            throw FloatValueException::invalid($value);
        }

        $value = bcadd(\sprintf('%.10f', $value), '0', self::DECIMALS);

        /* @see FloatValueType::getSQLDeclaration() */
        if ((float) $value >= 1e7 || (float) $value <= -1e7) {
            throw FloatValueException::outOfRange($value);
        }

        $this->value = $value;
    }

    #[\Override]
    public function __toString(): string
    {
        return $this->value;
    }

    #[\Override]
    public function compareTo($other): int
    {
        return bccomp($this->value, (string) $other, self::DECIMALS);
    }

    public function toFloat(?int $decimals = null): float
    {
        return (float) bcadd($this->value, '0', $decimals ?? self::DECIMALS);
    }

    public function add(int|float|string|self $valueToAdd): self
    {
        $valueToAdd = ($valueToAdd instanceof self) ? $valueToAdd : new self($valueToAdd);

        return new self(value: bcadd($this->value, (string) $valueToAdd, self::DECIMALS));
    }

    public function subtract(int|float|string|self $valueToSubtract): self
    {
        $valueToSubtract = ($valueToSubtract instanceof self) ? $valueToSubtract : new self($valueToSubtract);

        return new self(value: bcsub($this->value, (string) $valueToSubtract, self::DECIMALS));
    }

    public function multiply(int|float|string|self $multiplier): self
    {
        $multiplier = ($multiplier instanceof self) ? $multiplier : new self($multiplier);

        return new self(value: bcmul($this->value, (string) $multiplier, self::DECIMALS));
    }

    public function divide(int|float|string|self $divisor): self
    {
        $divisor = ($divisor instanceof self) ? $divisor : new self($divisor);

        if ($divisor->isZero()) {
            throw FloatValueException::divisionByZeroIsNotPermitted();
        }

        return new self(value: bcdiv($this->value, (string) $divisor, self::DECIMALS));
    }

    /**
     * PHP bcmath doesn't support round natively.
     *
     * @param 1|2|3|4 $mode
     *
     * @see https://www.php.net/bcmath
     * @see https://www.php.net/round
     */
    public function round(int $precision = 0, int $mode = \PHP_ROUND_HALF_UP): self
    {
        return new self(value: round($this->toFloat(decimals: self::DECIMALS), $precision, $mode));
    }

    /**
     * @see https://3v4l.org/e8gVB
     */
    public function isZero(): bool
    {
        return 0. === $this->toFloat();
    }

    public function isNegative(): bool
    {
        return $this->toFloat() < 0;
    }
}
