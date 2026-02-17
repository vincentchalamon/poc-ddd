<?php

declare(strict_types=1);

use App\Drawer\Domain\Model\EmailAddress;
use App\Drawer\Infrastructure\Doctrine\DBAL\Types\EmailAddressType;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $containerConfigurator->extension('doctrine', [
        'dbal' => [
            'types' => [
                EmailAddressType::class => EmailAddressType::class,
                EmailAddress::class => EmailAddressType::class,
            ],
        ],
        'orm' => [
            'mappings' => [
                'Drawer' => [
                    'type' => 'attribute',
                    'is_bundle' => false,
                    'dir' => '%kernel.project_dir%/src/Drawer/Domain/Model',
                    'prefix' => 'App\Drawer\Domain\Model',
                    'alias' => 'Drawer',
                ],
            ],
        ],
    ]);

    $containerConfigurator->extension('api_platform', [
        'mapping' => [
            'paths' => ['%kernel.project_dir%/src/Drawer/Domain/Model'],
        ],
    ]);

    // Custom ReflectionExtractor for {@see SockDenormalizer}
    $services->set('app.drawer.sock_reflection_extractor', ReflectionExtractor::class)
        ->args([
            '$mutatorPrefixes' => [], // Disable detection via set...
            '$accessorPrefixes' => [], // Disable detection via get...
            '$arrayMutatorPrefixes' => [], // Disable detection via add...
            '$enableConstructorExtraction' => true,
            '$accessFlags' => ReflectionProperty::IS_PRIVATE | ReflectionProperty::IS_PROTECTED | ReflectionProperty::IS_PUBLIC,
        ]);

    // Custom PropertyInfoExtractor for {@see SockDenormalizer}
    $services->set('app.drawer.sock_property_info', PropertyInfoExtractor::class)
        ->args([
            '$listExtractors' => [service('app.drawer.sock_reflection_extractor')],
            '$typeExtractors' => [service('app.drawer.sock_reflection_extractor')],
            '$descriptionExtractors' => [],
            '$accessExtractors' => [],
            '$initializableExtractors' => [],
        ]);

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->load('App\Drawer\\', __DIR__.'/../../../')
        ->autowire()
        ->autoconfigure()
        ->exclude([
            __DIR__.'/../../../Domain/{Exception,Model,Repository}',
            __DIR__,
        ])
    ;
};
