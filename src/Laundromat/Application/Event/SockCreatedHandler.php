<?php

declare(strict_types=1);

namespace App\Laundromat\Application\Event;

use App\Drawer\Infrastructure\Event\SockCreated;
use App\Laundromat\Domain\Exception\SockNotFoundException;
use App\Laundromat\Domain\Repository\SockRepositoryInterface;

final readonly class SockCreatedHandler
{
    public function __construct(
        private SockRepositoryInterface $sockRepository,
    ) {
    }

    /**
     * @throws SockNotFoundException
     */
    public function __invoke(SockCreated $event): void
    {
        $sock = $this->sockRepository->get($event->identifier);
        $sock->increaseCredits(5);

        $this->sockRepository->add($sock);
    }
}
