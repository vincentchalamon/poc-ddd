<?php

declare(strict_types=1);

namespace App\Drawer\Domain\Repository;

use App\Drawer\Domain\Exception\SockNotFoundException;
use App\Drawer\Domain\Model\Sock;
use App\Shared\Domain\Identifier\Identifier;

interface SockRepositoryInterface
{
    public function add(Sock $sock): void;

    /**
     * @throws SockNotFoundException
     */
    public function get(Identifier $identifier): Sock;
}
