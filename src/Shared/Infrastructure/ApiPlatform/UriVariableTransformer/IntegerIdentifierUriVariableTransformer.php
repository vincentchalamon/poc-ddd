<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\ApiPlatform\UriVariableTransformer;

use ApiPlatform\Metadata\UriVariableTransformerInterface;
use App\Shared\Domain\Identifier\Identifier;
use App\Shared\Infrastructure\Identifier\IntegerIdentifier;

final readonly class IntegerIdentifierUriVariableTransformer implements UriVariableTransformerInterface
{
    #[\Override]
    public function transform(mixed $value, array $types, array $context = []): Identifier
    {
        return new IntegerIdentifier($value);
    }

    #[\Override]
    public function supportsTransformation(mixed $value, array $types, array $context = []): bool
    {
        return Identifier::class === $types[0] && IntegerIdentifier::isValid($value);
    }
}
