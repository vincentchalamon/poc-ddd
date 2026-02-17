<?php

declare(strict_types=1);

namespace App\Drawer\Infrastructure\Doctrine\DBAL\Types;

use App\Drawer\Domain\Model\Size;
use App\Shared\Infrastructure\Doctrine\DBAL\Types\Float\FloatValueType;
use Doctrine\DBAL\Platforms\AbstractPlatform;

/**
 * Doctrine DBAL type to handle {@see Size} conversion and database storage.
 * Overrides {@see FloatValueType}.
 *
 * <code>
 * // in Doctrine ORM Entity
 * #[ORM\Column(type: Size::class)]
 * public Size $value;
 * </code>
 *
 * <code>
 * // can be nullable
 * #[ORM\Column(type: Size::class, nullable: true)]
 * public ?Size $value = null;
 * </code>
 *
 * @extends FloatValueType
 */
final class SizeType extends FloatValueType
{
    /**
     * @param string|null $value
     */
    #[\Override]
    public function convertToPHPValue($value, AbstractPlatform $platform): ?Size
    {
        if (null === $value || '' === $value) {
            return null;
        }

        return new Size($value);
    }
}
