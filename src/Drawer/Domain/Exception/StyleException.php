<?php

declare(strict_types=1);

namespace App\Drawer\Domain\Exception;

use App\Shared\Domain\Exception\UnprocessableThrowable;

final class StyleException extends \DomainException implements UnprocessableThrowable
{
    public static function fromMissingKeywords(): self
    {
        return new self('At least one keyword is required');
    }
}
