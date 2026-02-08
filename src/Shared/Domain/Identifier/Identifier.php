<?php

declare(strict_types=1);

namespace App\Shared\Domain\Identifier;

use App\Shared\Domain\Comparable\Comparable;

/**
 * When used on a resource, it passes through {@see IdentifierPropertyMetadataFactory} that will add some metadata.
 * Set the schema type to force its type.
 *
 * Usage with {@see IntegerIdentifier}:
 * <code>
 * #[ApiProperty(schema: ['type' => 'integer')]
 * public Identifier $id;
 * </code>
 *
 * Usage with {@see StringIdentifier}:
 * <code>
 * #[ApiProperty(schema: ['type' => 'string')]
 * public Identifier $id;
 * </code>
 *
 * Usage with {@see UuidIdentifier} (default):
 * <code>
 * #[ApiProperty(schema: ['type' => 'string', 'format' => 'uuid')]
 * public Identifier $id;
 * </code>
 */
interface Identifier extends \Stringable, Comparable
{
    /**
     * Returns true when the given value is a valid value for the current identifier type. False otherwise. Each
     * validation method can be different according to the identifier type.
     *
     * @param mixed $valueToValidate The value to validate
     *
     * @return bool True if the value is valid for the current identifier type, false otherwise
     */
    public static function isValid(mixed $valueToValidate): bool;
}
