<?php

declare(strict_types=1);

namespace App\Laundromat\Infrastructure\ApiPlatform\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Laundromat\Application\Query\GetSock;
use App\Laundromat\Domain\Exception\SockNotFoundException;
use App\Laundromat\Infrastructure\ApiPlatform\ApiResource\Sock as SockResource;
use App\Shared\Application\Bus\QueryBusInterface;
use App\Shared\Domain\Identifier\Identifier;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;

/**
 * @implements ProviderInterface<SockResource>
 */
final readonly class GetSockProvider implements ProviderInterface
{
    public function __construct(
        private QueryBusInterface $queryBus,
        private ObjectMapperInterface $objectMapper,
    ) {
    }

    /**
     * @param array{id: Identifier} $uriVariables
     *
     * @throws SockNotFoundException
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): SockResource
    {
        return $this->objectMapper->map($this->queryBus->dispatch(new GetSock($uriVariables['id'])), SockResource::class);
    }
}
