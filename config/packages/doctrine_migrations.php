<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('doctrine_migrations', [
        'migrations_paths' => [
            'DoctrineMigrations' => '%kernel.project_dir%/migrations',
        ],
        'enable_profiler' => false,
        // It's essential for our data migrations that run after schema migrations, preventing partial
        // migrations where schema changes succeed but data migrations fail. Makes replaying easier.
        'all_or_nothing' => true,
    ]);
};
