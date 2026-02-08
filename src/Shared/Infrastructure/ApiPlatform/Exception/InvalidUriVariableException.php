<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\ApiPlatform\Exception;

final class InvalidUriVariableException extends \LogicException
{
    public static function missingUriVariableTransformer(mixed $value): self
    {
        return new self(\sprintf('Unable to find an UriVariableTransformer for the current identifier of "%s".', get_debug_type($value)));
    }

    private function __construct(string $message)
    {
        parent::__construct($message);
    }
}
