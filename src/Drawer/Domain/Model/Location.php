<?php

declare(strict_types=1);

namespace App\Drawer\Domain\Model;

use App\Drawer\Domain\Exception\LocationException;

final readonly class Location implements \Stringable
{
    private const int MIN_LATITUDE = 0;
    private const int MAX_LATITUDE = 90;
    private const int MIN_LONGITUDE = -180;
    private const int MAX_LONGITUDE = 180;

    public function __construct(
        private float $latitude,
        private float $longitude,
    ) {
        if ($this->latitude < self::MIN_LATITUDE || $this->latitude > self::MAX_LATITUDE) {
            throw LocationException::fromLatitude(self::MIN_LATITUDE, self::MAX_LATITUDE);
        }

        if ($this->longitude < self::MIN_LONGITUDE || $this->longitude > self::MAX_LONGITUDE) {
            throw LocationException::fromLongitude(self::MIN_LONGITUDE, self::MAX_LONGITUDE);
        }
    }

    /**
     * @see https://www.iso.org/standard/75147.html
     */
    #[\Override]
    public function __toString(): string
    {
        return sprintf(
            '%s%s/',
            (string) ($this->latitude >= 0 ? sprintf('+%f', $this->latitude) : $this->latitude),
            (string) ($this->longitude >= 0 ? sprintf('+%f', $this->longitude) : $this->longitude),
        );
    }

    public function latitude(): float
    {
        return $this->latitude;
    }

    public function longitude(): float
    {
        return $this->longitude;
    }
}
