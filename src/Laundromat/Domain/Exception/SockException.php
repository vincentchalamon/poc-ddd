<?php

declare(strict_types=1);

namespace App\Laundromat\Domain\Exception;

use App\Shared\Domain\Exception\UnprocessableThrowable;
use BcMath\Number;

final class SockException extends \DomainException implements UnprocessableThrowable
{
    public static function unsufficientCredits(Number|string|int $credits, Number $actualCredits): self
    {
        return new self(sprintf('Credits are unsufficient, try to subtract "%s" to "%s"', $credits, $actualCredits));
    }
}
