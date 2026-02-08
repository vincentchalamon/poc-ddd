<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Doctrine\DBAL\Types\Text;

use Doctrine\DBAL\Types\TextType;

/**
 * Doctrine DBAL type to handle {@see NonEmptyString} conversion and database storage.
 * Use "TEXT" database type.
 *
 * <code>
 * // in Doctrine ORM Entity
 * #[ORM\Column(type: NonEmptyTextType::class)]
 * public NonEmptyString $value;
 * </code>
 *
 * <code>
 * // can be nullable
 * #[ORM\Column(type: NonEmptyTextType::class, nullable: true)]
 * public ?NonEmptyString $value = null;
 * </code>
 */
final class NonEmptyTextType extends TextType
{
    use NonEmptyStringTypeTrait;
}
