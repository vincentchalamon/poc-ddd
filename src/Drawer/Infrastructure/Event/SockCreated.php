<?php

declare(strict_types=1);

namespace App\Drawer\Infrastructure\Event;

use App\Shared\Domain\Identifier\Identifier;

final readonly class SockCreated
{
    public function __construct(
        public Identifier $identifier,
    ) {
    }
}
