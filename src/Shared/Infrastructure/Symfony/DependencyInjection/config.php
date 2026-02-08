<?php

declare(strict_types=1);

use App\Shared\Infrastructure\Doctrine\DBAL\Types\Identifier\IntegerIdentifierType;
use App\Shared\Infrastructure\Doctrine\DBAL\Types\Identifier\StringIdentifierType;
use App\Shared\Infrastructure\Doctrine\DBAL\Types\Identifier\UuidIdentifierType;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $containerConfigurator->extension('doctrine', [
        'dbal' => [
            'types' => [
                UuidIdentifierType::class => UuidIdentifierType::class,
                StringIdentifierType::class => StringIdentifierType::class,
                IntegerIdentifierType::class => IntegerIdentifierType::class,
            ],
        ],
    ]);

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->load('App\Shared\\', __DIR__.'/../../../')
        ->autowire()
        ->autoconfigure()
        ->exclude([
            __DIR__.'/../../../Domain',
            __DIR__.'/../../Identifier',
            __DIR__.'/../../Doctrine',
            __DIR__.'/../../ApiPlatform/Doctrine',
            __DIR__.'/../../ApiPlatform/Exception',
            __DIR__,
        ])
    ;
};
