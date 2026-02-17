<?php

declare(strict_types=1);

namespace App\Drawer\Domain\Exception;

use App\Shared\Domain\Exception\UnprocessableThrowable;

final class EmailAddressException extends \DomainException implements UnprocessableThrowable
{
    public static function create(string $emailAddress): self
    {
        return new self(sprintf('Email address "%s" is invalid', $emailAddress));
    }
}
