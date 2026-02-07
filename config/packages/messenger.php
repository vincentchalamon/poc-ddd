<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('framework', [
        'messenger' => [
            'failure_transport' => 'failed',
            'transports' => [
                'sync' => 'sync://',
                'async' => [
                    'dsn' => '%env(MESSENGER_TRANSPORT_DSN)%/async_messages',
                    'retry_strategy' => [
                        'max_retries' => 3,
                    ],
                ],
                'failed' => '%env(MESSENGER_TRANSPORT_DSN)%/failed_messages',
            ],
        ],
    ]);
};
