<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\ApiPlatform\UriVariableTransformer;

use ApiPlatform\Metadata\UriVariableTransformerInterface;
use App\Shared\Domain\Identifier\Identifier;
use App\Shared\Infrastructure\Identifier\UuidIdentifier;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

// Higher priority than {@see StringIdentifierUriVariableTransformer}
#[AsTaggedItem(priority: 1000)]
final readonly class UuidIdentifierUriVariableTransformer implements UriVariableTransformerInterface
{
    #[\Override]
    public function transform(mixed $value, array $types, array $context = []): Identifier
    {
        return new UuidIdentifier($value);
    }

    #[\Override]
    public function supportsTransformation(mixed $value, array $types, array $context = []): bool
    {
        return Identifier::class === $types[0] && UuidIdentifier::isValid($value);
    }
}
