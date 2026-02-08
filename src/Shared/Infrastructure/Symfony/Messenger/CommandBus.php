<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Symfony\Messenger;

use App\Shared\Application\Bus\CommandBusInterface;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @see https://symfony.com/doc/current/messenger.html
 */
final readonly class CommandBus implements CommandBusInterface
{
    public function __construct(
        private MessageBusInterface $commandBus,
    ) {
    }

    #[\Override]
    public function dispatch(object $command): void
    {
        $this->commandBus->dispatch($command);
    }
}
