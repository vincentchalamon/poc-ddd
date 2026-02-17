<?php

declare(strict_types=1);

namespace App\Drawer\Domain\Model;

use App\Drawer\Domain\Exception\SizeException;
use App\Shared\Domain\Number\FloatValue;

/**
 * @extends FloatValue
 */
final readonly class Size extends FloatValue
{
    public function __construct(
        int|float|string $size,
    ) {
        parent::__construct($size);

        // Maximum size is 250cm, minimum size is 100cm
        if ((float) $size > 250 || (float) $size < 100) {
            throw SizeException::create($size);
        }
    }
}
