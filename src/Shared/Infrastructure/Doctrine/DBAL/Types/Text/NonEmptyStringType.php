<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Doctrine\DBAL\Types\Text;

use Doctrine\DBAL\Types\StringType;

/**
 * Doctrine DBAL type to handle {@see NonEmptyString} conversion and database storage.
 * Use "STRING" database type.
 *
 * <code>
 * // in Doctrine ORM Entity
 * #[ORM\Column(type: NonEmptyStringType::class)]
 * public NonEmptyString $value;
 * </code>
 *
 * <code>
 * // can be nullable
 * #[ORM\Column(type: NonEmptyStringType::class, nullable: true)]
 * public ?NonEmptyString $value = null;
 * </code>
 */
final class NonEmptyStringType extends StringType
{
    use NonEmptyStringTypeTrait;
}
