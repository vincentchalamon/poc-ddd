<?php

declare(strict_types=1);

namespace App\Laundromat\Domain\Repository;

use App\Laundromat\Domain\Exception\SockNotFoundException;
use App\Laundromat\Domain\Model\Sock;
use App\Shared\Domain\Identifier\Identifier;
use Doctrine\Common\Collections\Collection;

interface SockRepositoryInterface
{
    /**
     * @throws SockNotFoundException
     */
    public function add(Sock $sock): void;

    /**
     * @throws SockNotFoundException
     */
    public function get(Identifier $identifier): Sock;

    /**
     * @return Collection<Sock>
     */
    public function list(): Collection;
}
