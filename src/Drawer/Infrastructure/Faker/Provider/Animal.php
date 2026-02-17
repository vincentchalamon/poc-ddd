<?php

declare(strict_types=1);

namespace App\Drawer\Infrastructure\Faker\Provider;

use App\Drawer\Domain\Factory\NameFactory;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\DependencyInjection\Attribute\When;

#[AutoconfigureTag('foundry.faker_provider')]
#[When('dev')]
#[When('test')]
final class Animal
{
    public function adjectiveAnimal(): string
    {
        return (string) new NameFactory()->create();
    }
}
