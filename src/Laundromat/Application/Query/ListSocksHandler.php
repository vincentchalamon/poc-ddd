<?php

declare(strict_types=1);

namespace App\Laundromat\Application\Query;

use App\Laundromat\Application\Model\Sock as SockDTO;
use App\Laundromat\Domain\Model\Sock;
use App\Laundromat\Domain\Repository\SockRepositoryInterface;
use App\Shared\Infrastructure\Doctrine\Common\Collections\MappedCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;

final readonly class ListSocksHandler
{
    public function __construct(
        private SockRepositoryInterface $sockRepository,
        private ObjectMapperInterface $objectMapper,
    ) {
    }

    /**
     * @return Collection<SockDTO>
     */
    public function __invoke(ListSocks $query): Collection
    {
        return new MappedCollection(
            collection: $this->sockRepository->list(),
            mapper: fn (Sock $sock): SockDTO => $this->objectMapper->map($sock, SockDTO::class),
        );
    }
}
