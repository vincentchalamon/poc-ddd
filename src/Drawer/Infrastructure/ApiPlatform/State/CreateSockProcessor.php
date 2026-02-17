<?php

declare(strict_types=1);

namespace App\Drawer\Infrastructure\ApiPlatform\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Drawer\Domain\Model\Sock;
use App\Drawer\Infrastructure\Doctrine\Repository\SockRepository;

/**
 * @implements ProcessorInterface<Sock>
 */
final readonly class CreateSockProcessor implements ProcessorInterface
{
    public function __construct(
        private SockRepository $sockRepository,
    ) {
    }

    /**
     * @param Sock $data
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Sock
    {
        $this->sockRepository->add($data);

        return $data;
    }
}
