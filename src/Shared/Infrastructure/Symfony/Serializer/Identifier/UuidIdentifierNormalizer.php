<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Symfony\Serializer\Identifier;

use ApiPlatform\Metadata\Property\Factory\PropertyMetadataFactoryInterface;
use App\Shared\Domain\Identifier\Identifier;
use App\Shared\Infrastructure\Identifier\UuidIdentifier;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final readonly class UuidIdentifierNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public function __construct(
        private PropertyMetadataFactoryInterface $propertyMetadataFactory,
    ) {
    }

    /**
     * @param UuidIdentifier $data
     */
    #[\Override]
    public function normalize(mixed $data, ?string $format = null, array $context = []): string
    {
        return (string) $data;
    }

    #[\Override]
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof UuidIdentifier;
    }

    /**
     * @param string $data
     */
    #[\Override]
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): UuidIdentifier
    {
        if (!\is_string($data) || !UuidIdentifier::isValid($data)) {
            throw NotNormalizableValueException::createForUnexpectedDataType(message: 'Invalid UUID identifier.', data: $data, expectedTypes: ['uuid'], path: $context['deserialization_path'] ?? null, useMessageForUser: true);
        }

        return new UuidIdentifier($data);
    }

    #[\Override]
    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        if (!is_a($type, Identifier::class, true)) {
            return false;
        }

        if (UuidIdentifier::class === $type) {
            return true;
        }

        // cannot detect UuidIdentifier because typehint is probably Identifier
        // detect UuidIdentifier from metadata
        $propertyMetadata = $this->propertyMetadataFactory->create($context['operation']->getClass(), $context['deserialization_path'] ?? '');
        $schema = $propertyMetadata->getSchema();

        $schemaType = $schema['type'] ?? null;
        if (!$schemaType) {
            return false;
        }

        $schemaType = \is_array($schemaType) ? $schemaType : [$schemaType];

        return \in_array('string', $schemaType, true) && 'uuid' === ($schema['format'] ?? null);
    }

    #[\Override]
    public function getSupportedTypes(?string $format): array
    {
        return [
            // Don't cache as multiple Normalizer can support the Identifier interface.
            // The support depends on the propertyMetadata Schema.
            Identifier::class => false,
            UuidIdentifier::class => true,
        ];
    }
}
