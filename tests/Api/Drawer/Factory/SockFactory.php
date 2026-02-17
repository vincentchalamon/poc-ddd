<?php

declare(strict_types=1);

namespace App\Tests\Api\Drawer\Factory;

use App\Drawer\Domain\Model\EmailAddress;
use App\Drawer\Domain\Model\Sock;
use App\Drawer\Domain\Model\Style;
use App\Shared\Domain\Text\NonEmptyString;
use App\Shared\Infrastructure\Identifier\UuidIdentifier;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<Sock>
 */
final class SockFactory extends PersistentObjectFactory
{
    public static function class(): string
    {
        return Sock::class;
    }

    protected function defaults(): array|callable
    {
        return [
            'identifier' => new UuidIdentifier(),
            'emailAddress' => new EmailAddress(self::faker()->unique()->safeEmail()),
            'name' => new NonEmptyString(self::faker()->unique()->adjectiveAnimal()),
            'style' => null,
        ];
    }

    public function withStyle(Style $style): self
    {
        return $this->with(['style' => $style]);
    }
}
