<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    if (\in_array($containerConfigurator->env(), ['dev', 'test'], true)) {
        $containerConfigurator->extension('zenstruck_foundry', [
            'orm' => [
                'reset' => [
                    'entity_managers' => ['default'],
                    'mode' => 'migrate',
                ],
            ],
            'faker' => [
                'locale' => 'en_GB',
                'seed' => 123456, // The seed is required here to always use the same date in the generated fixtures
            ],
            'persistence' => [
                'flush_once' => true,
            ],
        ]);

        $services = $containerConfigurator->services();

        $services->load('App\Fixtures\\', __DIR__.'/../../fixtures')
            ->autowire()
            ->autoconfigure();
    }
};
