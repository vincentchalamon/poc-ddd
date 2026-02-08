<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Identifier;

use App\Shared\Domain\Identifier\Identifier;

final readonly class IntegerIdentifier implements Identifier
{
    private string $value;

    public function __construct(int|string $identifier)
    {
        $this->value = (string) $identifier;
    }

    #[\Override]
    public static function isValid(mixed $valueToValidate): bool
    {
        if (\is_int($valueToValidate)) {
            return true;
        }

        return \is_string($valueToValidate) && filter_var($valueToValidate, \FILTER_VALIDATE_INT);
    }

    /**
     * @param Identifier $objectToCompare
     */
    #[\Override]
    public function compareTo(mixed $objectToCompare): bool
    {
        if (!$objectToCompare instanceof self) {
            throw new \InvalidArgumentException(\sprintf('Argument $a must be an instance of "%s", "%s" given.', self::class, $objectToCompare::class));
        }

        return $this->value === $objectToCompare->__toString();
    }

    #[\Override]
    public function __toString(): string
    {
        return $this->value;
    }
}
