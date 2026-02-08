<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\ApiPlatform\UriVariableTransformer;

use ApiPlatform\Metadata\UriVariableTransformerInterface;
use App\Shared\Domain\Identifier\Identifier;
use App\Shared\Infrastructure\ApiPlatform\Exception\InvalidUriVariableException;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem(priority: -255)]
final readonly class IdentifierUriVariableTransformer implements UriVariableTransformerInterface
{
    #[\Override]
    public function transform(mixed $value, array $types, array $context = []): Identifier
    {
        // This UriVariableTransformer should never be called. The uuid, string and integer transformers should have
        // been called before to transform a string/int value into an identifier object. If this transformer is reached,
        // it means there is no available transformer for the current identifier type
        throw InvalidUriVariableException::missingUriVariableTransformer($value);
    }

    #[\Override]
    public function supportsTransformation(mixed $value, array $types, array $context = []): bool
    {
        return Identifier::class === $types[0];
    }
}
