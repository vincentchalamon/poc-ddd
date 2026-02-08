<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\ApiPlatform\UriVariableTransformer;

use ApiPlatform\Metadata\UriVariableTransformerInterface;
use App\Shared\Domain\Identifier\Identifier;
use App\Shared\Infrastructure\Identifier\StringIdentifier;

final readonly class StringIdentifierUriVariableTransformer implements UriVariableTransformerInterface
{
    #[\Override]
    public function transform(mixed $value, array $types, array $context = []): Identifier
    {
        return new StringIdentifier($value);
    }

    #[\Override]
    public function supportsTransformation(mixed $value, array $types, array $context = []): bool
    {
        return Identifier::class === $types[0] && StringIdentifier::isValid($value);
    }
}
