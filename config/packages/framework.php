<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('framework', [
        'secret' => '%env(APP_SECRET)%',
        'trusted_proxies' => '%env(TRUSTED_PROXIES)%',
        'trusted_hosts' => '%env(TRUSTED_HOSTS)%',
        'trusted_headers' => [
            'x-forwarded-for',
            'x-forwarded-proto',
        ],
    ]);
    if ('test' === $containerConfigurator->env()) {
        $containerConfigurator->extension('framework', [
            'test' => true,
        ]);
    }
};
