<?php

declare(strict_types=1);

namespace App\Drawer\Infrastructure\Doctrine\DBAL\Types;

use App\Drawer\Domain\Model\Location;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Exception\InvalidType;
use Doctrine\DBAL\Types\JsonType;

/**
 * Doctrine DBAL type to handle {@see Location} conversion and database storage.
 * Use "JSON" database type.
 *
 * <code>
 * // in Doctrine ORM Entity
 * #[ORM\Column(type: Location::class)]
 * public Location $value;
 * </code>
 *
 * <code>
 * // can be nullable
 * #[ORM\Column(type: Location::class, nullable: true)]
 * public ?Location $value = null;
 * </code>
 *
 * @extends JsonType
 */
final class LocationType extends JsonType
{
    public const string TYPE = 'money';

    #[\Override]
    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if (null === $value) {
            return null;
        }

        if (!$value instanceof Location) {
            throw InvalidType::new($value, self::TYPE, ['null', Location::class]);
        }

        return parent::convertToDatabaseValue([
            'latitude' => $value->latitude(),
            'longitude' => $value->longitude(),
        ], $platform);
    }

    #[\Override]
    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?Location
    {
        if (null === $value || '' === $value) {
            return null;
        }

        /** @var array{value: string, currency: string} $data */
        $data = parent::convertToPHPValue($value, $platform);
        if (!\is_array($data)) {
            return $value;
        }

        return new Location(...$data);
    }
}
