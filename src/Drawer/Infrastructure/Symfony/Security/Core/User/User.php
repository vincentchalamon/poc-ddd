<?php

declare(strict_types=1);

namespace App\Drawer\Infrastructure\Symfony\Security\Core\User;

use App\Drawer\Domain\Model\Sock;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * This class is a simple implementation of the {@see UserInterface}.
 *
 * It must be decoupled from {@see Sock} to avoid coupling the domain model with the security component.
 *
 * @implements UserInterface
 */
final readonly class User implements UserInterface
{
    public function __construct(
        private Sock $sock,
    ) {
    }

    #[\Override]
    public function getUserIdentifier(): string
    {
        return (string) $this->sock->emailAddress();
    }

    #[\Override]
    public function getRoles(): array
    {
        return ['ROLE_USER'];
    }
}
