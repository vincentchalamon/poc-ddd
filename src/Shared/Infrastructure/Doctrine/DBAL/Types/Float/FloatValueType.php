<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Doctrine\DBAL\Types\Float;

use App\Shared\Domain\Number\FloatValue;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Exception\InvalidType;
use Doctrine\DBAL\Types\Type;

/**
 * Doctrine DBAL type to handle ${@see FloatValue} conversion and database storage.
 * Use "NUMERIC" database type.
 *
 * <code>
 * // in Doctrine ORM Entity
 * #[ORM\Column(type: FloatValue::class)]
 * public FloatValue $value;
 * </code>
 *
 * <code>
 * // can be nullable
 * #[ORM\Column(type: FloatValue::class, nullable: true)]
 * public ?FloatValue $value = null;
 * </code>
 *
 * @see https://www.postgresql.org/docs/current/datatype-numeric.html
 */
final class FloatValueType extends Type
{
    public const string TYPE = 'float_value';

    /**
     * Force scale and precision.
     *
     * @see AbstractPlatform::getDecimalTypeDeclarationSQL()
     */
    #[\Override]
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        if (!($column['scale'] ?? null)) {
            $column['scale'] = FloatValue::DECIMALS;
        }

        if (!($column['precision'] ?? null)) {
            $column['precision'] = 7 + FloatValue::DECIMALS;
        }

        return $platform->getDecimalTypeDeclarationSQL($column);
    }

    #[\Override]
    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if (null === $value) {
            return null;
        }

        if (!$value instanceof FloatValue) {
            throw InvalidType::new($value, self::TYPE, ['null', FloatValue::class]);
        }

        return parent::convertToDatabaseValue(value: (string) $value, platform: $platform);
    }

    #[\Override]
    public function convertToPHPValue($value, AbstractPlatform $platform): ?FloatValue
    {
        if (null === $value || '' === $value) {
            return null;
        }

        return parent::convertToPHPValue(new FloatValue(value: $value), $platform);
    }
}
