<?php

declare(strict_types=1);

namespace App\Laundromat\Application\Query;

use App\Laundromat\Application\Model\Sock;
use App\Laundromat\Domain\Repository\SockRepositoryInterface;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;

final readonly class GetSockHandler
{
    public function __construct(
        private SockRepositoryInterface $sockRepository,
        private ObjectMapperInterface $objectMapper,
    ) {
    }

    public function __invoke(GetSock $query): Sock
    {
        return $this->objectMapper->map($this->sockRepository->get($query->identifier), Sock::class);
    }
}
