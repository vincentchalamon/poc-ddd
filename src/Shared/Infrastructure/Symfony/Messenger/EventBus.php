<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Symfony\Messenger;

use App\Shared\Application\Bus\EventBusInterface;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @see https://symfony.com/doc/current/messenger.html
 */
final readonly class EventBus implements EventBusInterface
{
    public function __construct(
        private MessageBusInterface $eventBus,
    ) {
    }

    #[\Override]
    public function dispatch(object $event): void
    {
        $this->eventBus->dispatch($event);
    }
}
