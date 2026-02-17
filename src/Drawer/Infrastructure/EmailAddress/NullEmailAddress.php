<?php

declare(strict_types=1);

namespace App\Drawer\Infrastructure\EmailAddress;

use App\Drawer\Domain\Model\EmailAddress;
use App\Shared\Domain\Exception\UnprocessableException;

final readonly class NullEmailAddress extends EmailAddress
{
    public function __construct()
    {
        // Disable parent constructor to prevent validation of the email address.
    }

    #[\Override]
    public function __toString(): string
    {
        throw new UnprocessableException('NullEmailAddress cannot be converted to string.');
    }

    #[\Override]
    public function compareTo($other): int
    {
        return -1;
    }

    #[\Override]
    public function text(): string
    {
        throw new UnprocessableException('NullEmailAddress does not have a text representation.');
    }

    #[\Override]
    public function domain(): string
    {
        throw new UnprocessableException('NullEmailAddress does not have a domain.');
    }

    #[\Override]
    public function topLevelDomain(): string
    {
        throw new UnprocessableException('NullEmailAddress does not have a topLevelDomain.');
    }

    #[\Override]
    public function length(): int
    {
        return 0;
    }
}
