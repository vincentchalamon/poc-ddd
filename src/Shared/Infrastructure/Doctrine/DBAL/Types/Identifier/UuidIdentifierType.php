<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Doctrine\DBAL\Types\Identifier;

use App\Shared\Infrastructure\Identifier\UuidIdentifier;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\StringType;

/**
 * Doctrine DBAL type to handle {@see UuidIdentifier} conversion and database storage.
 * Use "STRING" database type.
 *
 * <code>
 * // in Doctrine ORM Entity
 * #[ORM\Column(type: UuidIdentifier::class)]
 * public Identifier $value;
 * </code>
 *
 * <code>
 * // can be nullable
 * #[ORM\Column(type: UuidIdentifier::class, nullable: true)]
 * public ?Identifier $value = null;
 * </code>
 */
final class UuidIdentifierType extends StringType
{
    /**
     * @param UuidIdentifier|string|null $value
     */
    #[\Override]
    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if (null === $value) {
            return null;
        }

        if (\is_string($value)) {
            if (!UuidIdentifier::isValid($value)) {
                throw new ConversionException(\sprintf('The uuid identifier value "%s" is not a valid uuid', $value));
            }

            return $value;
        }

        return $value->__toString();
    }

    /**
     * @param string|null $value
     */
    #[\Override]
    public function convertToPHPValue($value, AbstractPlatform $platform): ?UuidIdentifier
    {
        if (null === $value || '' === $value) {
            return null;
        }

        return new UuidIdentifier($value);
    }
}
