<?php

declare(strict_types=1);

namespace App\Laundromat\Application\Query;

use App\Shared\Domain\Identifier\Identifier;

final readonly class GetSock
{
    public function __construct(
        public Identifier $identifier,
    ) {
    }
}
