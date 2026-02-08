<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Doctrine\DBAL\Types\Identifier;

use App\Shared\Infrastructure\Identifier\StringIdentifier;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

/**
 * Doctrine DBAL type to handle {@see StringIdentifier} conversion and database storage.
 * Use "STRING" database type.
 *
 * <code>
 * // in Doctrine ORM Entity
 * #[ORM\Column(type: StringIdentifier::class)]
 * public Identifier $value;
 * </code>
 *
 * <code>
 * // can be nullable
 * #[ORM\Column(type: StringIdentifier::class, nullable: true)]
 * public ?Identifier $value = null;
 * </code>
 */
final class StringIdentifierType extends StringType
{
    /**
     * @param StringIdentifier|null $value
     */
    #[\Override]
    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        return $value?->__toString();
    }

    /**
     * @param string|null $value
     */
    #[\Override]
    public function convertToPHPValue($value, AbstractPlatform $platform): ?StringIdentifier
    {
        if (null === $value || '' === $value) {
            return null;
        }

        return new StringIdentifier($value);
    }
}
