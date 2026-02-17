<?php

declare(strict_types=1);

namespace App\Drawer\Domain\Exception;

use App\Shared\Domain\Exception\UnprocessableThrowable;

final class LocationException extends \DomainException implements UnprocessableThrowable
{
    public static function fromLatitude(int $minLatitude, int $maxLatitude): self
    {
        return new self(sprintf('The latitude is invalid. It should be greater than or equal to %d° and lesser than or equal to %d°', [$minLatitude, $maxLatitude]));
    }

    public static function fromLongitude(int $minLongitude, int $maxLongitude): self
    {
        return new self(sprintf('The longitude is invalid. It should be greater than or equal to %d° and lesser than or equal to %d°', [$minLongitude, $maxLongitude]));
    }
}
