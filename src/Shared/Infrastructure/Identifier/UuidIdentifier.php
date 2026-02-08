<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Identifier;

use App\Shared\Domain\Identifier\Identifier;
use Symfony\Component\Uid\AbstractUid;
use Symfony\Component\Uid\Uuid;

final readonly class UuidIdentifier implements Identifier
{
    private AbstractUid $value;

    public function __construct(string|AbstractUid|null $value = null)
    {
        $this->value = match (true) {
            null === $value => Uuid::v7(),
            \is_string($value) => Uuid::fromString($value),
            default => $value,
        };
    }

    #[\Override]
    public static function isValid(mixed $valueToValidate): bool
    {
        if (!\is_string($valueToValidate)) {
            return false;
        }

        return Uuid::isValid($valueToValidate);
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

        return $this->__toString() === $objectToCompare->__toString();
    }

    #[\Override]
    public function __toString(): string
    {
        return $this->value->__toString();
    }
}
