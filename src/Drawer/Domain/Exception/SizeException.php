<?php

declare(strict_types=1);

namespace App\Drawer\Domain\Exception;

use App\Shared\Domain\Exception\UnprocessableThrowable;

final class SizeException extends \DomainException implements UnprocessableThrowable
{
    public static function create(string $size): self
    {
        return new self(sprintf('Size "%scm" is out of range.', $size));
    }
}
