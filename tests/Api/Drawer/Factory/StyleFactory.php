<?php

declare(strict_types=1);

namespace App\Tests\Api\Drawer\Factory;

use App\Drawer\Domain\Model\Location;
use App\Drawer\Domain\Model\Size;
use App\Drawer\Domain\Model\Style;
use App\Shared\Domain\Text\NonEmptyString;
use Zenstruck\Foundry\ObjectFactory;

/**
 * @extends ObjectFactory<Style>
 */
final class StyleFactory extends ObjectFactory
{
    public static function class(): string
    {
        return Style::class;
    }

    protected function defaults(): array|callable
    {
        return [
            'size' => new Size(self::faker()->randomFloat(2, 100, 250)),
            'description' => new NonEmptyString(self::faker()->sentence()),
            'keywords' => [
                new NonEmptyString(self::faker()->word()),
                new NonEmptyString(self::faker()->word()),
                new NonEmptyString(self::faker()->word()),
            ],
            'location' => new Location(
                self::faker()->randomFloat(6, 0, 90),
                self::faker()->randomFloat(6, -180, 180)
            ),
        ];
    }
}
