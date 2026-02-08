<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Identifier;

use App\Shared\Domain\Identifier\Identifier;

final readonly class StringIdentifier implements Identifier
{
    public function __construct(private string $value)
    {
    }

    #[\Override]
    public static function isValid(mixed $valueToValidate): bool
    {
        return \is_string($valueToValidate);
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
