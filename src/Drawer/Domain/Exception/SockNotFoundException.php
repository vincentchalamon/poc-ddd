<?php

declare(strict_types=1);

namespace App\Drawer\Domain\Exception;

use App\Shared\Domain\Exception\DomainNotFoundThrowable;
use App\Shared\Domain\Identifier\Identifier;

final class SockNotFoundException extends \DomainException implements DomainNotFoundThrowable
{
    public static function create(Identifier $identifier): self
    {
        return new self(sprintf('Sock with id "%s" is not found.', $identifier));
    }
}
