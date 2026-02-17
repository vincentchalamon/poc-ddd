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
     * @param Identifier $other
     */
    #[\Override]
    public function compareTo($other): int
    {
        if (!$other instanceof self) {
            throw new \InvalidArgumentException(\sprintf('Argument $a must be an instance of "%s", "%s" given.', self::class, $other::class));
        }

        $compare = strcmp((string) $this, (string) $other);

        if ($compare < 0) {
            return -1;
        }

        if ($compare > 0) {
            return 1;
        }

        return 0;
    }

    #[\Override]
    public function __toString(): string
    {
        return $this->value;
    }
}
