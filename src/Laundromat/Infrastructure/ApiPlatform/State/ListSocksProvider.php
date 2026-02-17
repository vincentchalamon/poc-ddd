<?php

declare(strict_types=1);

namespace App\Laundromat\Infrastructure\ApiPlatform\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\PaginatorInterface;
use ApiPlatform\State\ProviderInterface;
use App\Laundromat\Application\Model\Sock as SockDTO;
use App\Laundromat\Application\Query\ListSocks;
use App\Laundromat\Infrastructure\ApiPlatform\ApiResource\Sock as SockResource;
use App\Shared\Application\Bus\QueryBusInterface;
use App\Shared\Infrastructure\ApiPlatform\Doctrine\Common\Collections\PaginatedCollection;
use App\Shared\Infrastructure\Doctrine\Common\Collections\MappedCollection;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;

/**
 * @implements ProviderInterface<PaginatorInterface<SockResource>>
 */
final readonly class ListSocksProvider implements ProviderInterface
{
    public function __construct(
        private QueryBusInterface $queryBus,
        private ObjectMapperInterface $objectMapper,
    ) {
    }

    /**
     * @param array{filters?: array{page?: int, itemsPerPage?: int}} $context
     *
     * @return PaginatorInterface<SockResource>
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): PaginatorInterface
    {
        // todo apply filters (FilteredCollection)
        return new PaginatedCollection(
            collection: new MappedCollection(
                collection: $this->queryBus->dispatch(new ListSocks()),
                mapper: fn (SockDTO $sock): SockResource => $this->objectMapper->map($sock, SockResource::class),
            ),
            page: $context['filters']['page'] ?? 1,
            itemsPerPage: $context['filters']['itemsPerPage'] ?? 30,
        );
    }
}
