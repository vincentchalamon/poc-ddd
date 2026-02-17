<?php

declare(strict_types=1);

namespace App\Drawer\Infrastructure\ApiPlatform\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Drawer\Domain\Exception\SockNotFoundException;
use App\Drawer\Domain\Model\Sock;
use App\Drawer\Infrastructure\Doctrine\Repository\SockRepository;
use App\Shared\Domain\Identifier\Identifier;

/**
 * @implements ProviderInterface<Sock>
 */
final readonly class GetSockProvider implements ProviderInterface
{
    public function __construct(
        private SockRepository $sockRepository,
    ) {
    }

    /**
     * @param array{identifier: Identifier} $uriVariables
     *
     * @throws SockNotFoundException
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): Sock
    {
        return $this->sockRepository->get($uriVariables['identifier']);
    }
}
