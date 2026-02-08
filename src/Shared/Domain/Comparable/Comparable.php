<?php

declare(strict_types=1);

namespace App\Shared\Domain\Comparable;

/**
 * Implementation of RFC Comparable.
 *
 * @see https://wiki.php.net/rfc/comparable
 */
interface Comparable
{
    /**
     * Returns true if the given object representation is equals to the current object representation.
     *
     * @param mixed $objectToCompare The object to compare with the current object
     *
     * @return bool True if the given object representation is equals to the current object representation, false otherwise
     */
    public function compareTo(mixed $objectToCompare): bool;
}
