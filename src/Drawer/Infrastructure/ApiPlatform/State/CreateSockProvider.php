<?php

declare(strict_types=1);

namespace App\Drawer\Infrastructure\ApiPlatform\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Drawer\Domain\Factory\NameFactory;
use App\Drawer\Domain\Model\Sock;
use App\Drawer\Infrastructure\EmailAddress\NullEmailAddress;
use App\Shared\Infrastructure\Identifier\UuidIdentifier;

/**
 * @implements ProviderInterface<Sock>
 */
final readonly class CreateSockProvider implements ProviderInterface
{
    public function __construct(
        private NameFactory $nameFactory,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): Sock
    {
        return new Sock(
            identifier: new UuidIdentifier(),
            emailAddress: new NullEmailAddress(),
            name: $this->nameFactory->create(),
        );
    }
}
