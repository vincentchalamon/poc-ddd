<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Symfony\Serializer\Identifier;

use ApiPlatform\Metadata\Property\Factory\PropertyMetadataFactoryInterface;
use App\Shared\Domain\Identifier\Identifier;
use App\Shared\Infrastructure\Identifier\StringIdentifier;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final readonly class StringIdentifierNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public function __construct(
        private PropertyMetadataFactoryInterface $propertyMetadataFactory,
    ) {
    }

    /**
     * @param StringIdentifier $data
     */
    #[\Override]
    public function normalize(mixed $data, ?string $format = null, array $context = []): string
    {
        return (string) $data;
    }

    #[\Override]
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof StringIdentifier;
    }

    /**
     * @param string $data
     */
    #[\Override]
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): StringIdentifier
    {
        if (!\is_string($data) || StringIdentifier::isValid($data)) {
            throw NotNormalizableValueException::createForUnexpectedDataType(message: 'Invalid string identifier.', data: $data, expectedTypes: ['string'], path: $context['deserialization_path'] ?? null, useMessageForUser: true);
        }

        return new StringIdentifier($data);
    }

    #[\Override]
    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        if (!is_a($type, Identifier::class, true)) {
            return false;
        }

        if (StringIdentifier::class === $type) {
            return true;
        }

        // cannot detect StringIdentifier because typehint is probably Identifier
        // detect StringIdentifier from metadata
        $propertyMetadata = $this->propertyMetadataFactory->create($context['operation']->getClass(), $context['deserialization_path'] ?? '');
        $schema = $propertyMetadata->getSchema();

        $schemaType = $schema['type'] ?? null;
        if (!$schemaType) {
            return false;
        }

        $schemaType = \is_array($schemaType) ? $schemaType : [$schemaType];

        return \in_array('string', $schemaType, true) && !($schema['format'] ?? null);
    }

    #[\Override]
    public function getSupportedTypes(?string $format): array
    {
        return [
            // Don't cache as multiple Normalizer can support the Identifier interface.
            // The support depends on the propertyMetadata Schema.
            Identifier::class => false,
            StringIdentifier::class => true,
        ];
    }
}
