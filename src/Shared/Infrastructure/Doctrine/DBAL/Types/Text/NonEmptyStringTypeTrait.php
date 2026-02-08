<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Doctrine\DBAL\Types\Text;

use App\Shared\Domain\Exception\NonEmptyStringException;
use App\Shared\Domain\Text\NonEmptyString;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;

trait NonEmptyStringTypeTrait
{
    /**
     * @param NonEmptyString|null $value
     */
    #[\Override]
    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if (null !== $value && !$value instanceof NonEmptyString) {
            throw new ConversionException(\sprintf('Expected instance of %s, got %s.', NonEmptyString::class, get_debug_type($value)));
        }

        return $value?->text();
    }

    #[\Override]
    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?NonEmptyString
    {
        if (null === $value || '' === $value) {
            return null;
        }

        try {
            return new NonEmptyString(text: $value);
        } catch (NonEmptyStringException $nonEmptyStringException) {
            throw new ConversionException(\sprintf('Failed to convert database value to NonEmptyString: %s.', $nonEmptyStringException->getMessage()), $nonEmptyStringException->getCode(), $nonEmptyStringException);
        }
    }
}
