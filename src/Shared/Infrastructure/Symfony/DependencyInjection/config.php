<?php

declare(strict_types=1);

use App\Shared\Domain\Number\FloatValue;
use App\Shared\Domain\Text\NonEmptyString;
use App\Shared\Infrastructure\Doctrine\DBAL\Types\Float\FloatValueType;
use App\Shared\Infrastructure\Doctrine\DBAL\Types\Identifier\IntegerIdentifierType;
use App\Shared\Infrastructure\Doctrine\DBAL\Types\Identifier\StringIdentifierType;
use App\Shared\Infrastructure\Doctrine\DBAL\Types\Identifier\UuidIdentifierType;
use App\Shared\Infrastructure\Doctrine\DBAL\Types\Text\NonEmptyStringType;
use App\Shared\Infrastructure\Doctrine\DBAL\Types\Text\NonEmptyTextType;
use App\Shared\Infrastructure\Identifier\IntegerIdentifier;
use App\Shared\Infrastructure\Identifier\StringIdentifier;
use App\Shared\Infrastructure\Identifier\UuidIdentifier;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $containerConfigurator->extension('doctrine', [
        'dbal' => [
            'types' => [
                UuidIdentifierType::class => UuidIdentifierType::class,
                UuidIdentifier::class => UuidIdentifierType::class,
                StringIdentifierType::class => StringIdentifierType::class,
                StringIdentifier::class => StringIdentifierType::class,
                IntegerIdentifierType::class => IntegerIdentifierType::class,
                IntegerIdentifier::class => IntegerIdentifierType::class,
                FloatValueType::class => FloatValueType::class,
                FloatValue::class => FloatValueType::class,
                NonEmptyStringType::class => NonEmptyStringType::class,
                NonEmptyTextType::class => NonEmptyStringType::class,
                NonEmptyString::class => NonEmptyStringType::class,
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
