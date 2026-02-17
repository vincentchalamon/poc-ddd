<?php

declare(strict_types=1);

namespace App\Laundromat\Infrastructure\Doctrine\Repository;

use App\Laundromat\Domain\Exception\SockNotFoundException;
use App\Laundromat\Domain\Model\Sock;
use App\Laundromat\Domain\Repository\SockRepositoryInterface;
use App\Laundromat\Infrastructure\Doctrine\Entity\Sock as SockEntity;
use App\Shared\Domain\Identifier\Identifier;
use App\Shared\Infrastructure\Doctrine\Common\Collections\MappedCollection;
use App\Shared\Infrastructure\Doctrine\ORM\QueryBuilderCollection;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;

final class SockRepository extends ServiceEntityRepository implements SockRepositoryInterface
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly ObjectMapperInterface $objectMapper,
    ) {
        parent::__construct($registry, SockEntity::class);
    }

    public function add(Sock $sock): void
    {
        $object = $this->find($sock->identifier());
        if (!$object) {
            throw SockNotFoundException::create($sock->identifier());
        }

        $em = $this->getEntityManager();
        $em->persist($sock);
        $em->flush();
    }

    public function get(Identifier $identifier): Sock
    {
        $object = $this->find($identifier);
        if (!$object) {
            throw SockNotFoundException::create($identifier);
        }

        return $this->objectMapper->map($object, Sock::class);
    }

    public function list(): Collection
    {
        return new MappedCollection(
            collection: new QueryBuilderCollection($this->createQueryBuilder('sock')),
            mapper: fn (SockEntity $sock): Sock => $this->objectMapper->map($sock, Sock::class),
        );
    }
}
