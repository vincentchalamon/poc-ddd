<?php

declare(strict_types=1);

namespace App\Drawer\Infrastructure\Doctrine\Repository;

use App\Drawer\Domain\Exception\SockNotFoundException;
use App\Drawer\Domain\Model\Sock;
use App\Drawer\Domain\Repository\SockRepositoryInterface;
use App\Drawer\Infrastructure\Event\SockCreated;
use App\Shared\Application\Bus\EventBusInterface;
use App\Shared\Domain\Identifier\Identifier;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Sock>
 */
final class SockRepository extends ServiceEntityRepository implements SockRepositoryInterface
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly EventBusInterface $eventBus,
    ) {
        parent::__construct($registry, Sock::class);
    }

    public function add(Sock $sock): void
    {
        $em = $this->getEntityManager();
        $isCreation = $em->contains($sock);

        $em->persist($sock);
        $em->flush();

        if ($isCreation) {
            $this->eventBus->dispatch(new SockCreated($sock->identifier()));
        }
    }

    public function get(Identifier $identifier): Sock
    {
        $object = $this->find($identifier);
        if (!$object instanceof Sock) {
            throw SockNotFoundException::create($identifier);
        }

        return $object;
    }
}
