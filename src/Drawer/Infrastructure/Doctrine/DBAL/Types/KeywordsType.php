<?php

declare(strict_types=1);

namespace App\Drawer\Infrastructure\Doctrine\DBAL\Types;

use App\Shared\Domain\Text\NonEmptyString;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Exception\InvalidType;
use Doctrine\DBAL\Types\JsonType;

/**
 * Doctrine DBAL type to handle {@see Style::$keywords} conversion and database storage.
 * Use "JSON" database type.
 *
 * <code>
 * // in Doctrine ORM Entity
 * #[ORM\Column(type: KeywordsType::class)]
 * public array $value;
 * </code>
 *
 * <code>
 * // can be nullable
 * #[ORM\Column(type: KeywordsType::class, nullable: true)]
 * public ?array $value = null;
 * </code>
 *
 * @extends JsonType
 */
final class KeywordsType extends JsonType
{
    #[\Override]
    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if (null === $value || [] === $value) {
            return null;
        }

        if (!is_array($value)) {
            throw InvalidType::new($value, self::class, ['null', 'array']);
        }

        return parent::convertToDatabaseValue(
            array_map(static fn (NonEmptyString $keyword): string => (string) $keyword, $value),
            $platform
        );
    }

    #[\Override]
    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?array
    {
        if (null === $value || '' === $value) {
            return null;
        }

        $data = parent::convertToPHPValue($value, $platform);
        if (!\is_array($data)) {
            return $value;
        }

        return array_map(static fn (string $keyword): NonEmptyString => new NonEmptyString($keyword), $data);
    }
}
