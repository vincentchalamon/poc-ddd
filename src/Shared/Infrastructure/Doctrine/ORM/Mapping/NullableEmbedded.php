<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Doctrine\ORM\Mapping;

/**
 * Marks an {@see Embedded} property as nullable, allowing it to be null in the database and in the entity.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
final readonly class NullableEmbedded
{
}
