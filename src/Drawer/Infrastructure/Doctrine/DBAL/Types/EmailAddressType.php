<?php

declare(strict_types=1);

namespace App\Drawer\Infrastructure\Doctrine\DBAL\Types;

use App\Drawer\Domain\Model\EmailAddress;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

/**
 * Doctrine DBAL type to handle {@see EmailAddress} conversion and database storage.
 * Use "STRING" database type.
 *
 * <code>
 * // in Doctrine ORM Entity
 * #[ORM\Column(type: EmailAddress::class)]
 * public EmailAddress $value;
 * </code>
 *
 * <code>
 * // can be nullable
 * #[ORM\Column(type: EmailAddress::class, nullable: true)]
 * public ?EmailAddress $value = null;
 * </code>
 */
final class EmailAddressType extends StringType
{
    /**
     * @param EmailAddress|null $value
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
    public function convertToPHPValue($value, AbstractPlatform $platform): ?EmailAddress
    {
        if (null === $value || '' === $value) {
            return null;
        }

        return new EmailAddress($value);
    }
}
