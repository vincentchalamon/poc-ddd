<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Symfony\Serializer\Identifier;

use ApiPlatform\Metadata\Property\Factory\PropertyMetadataFactoryInterface;
use App\Shared\Domain\Identifier\Identifier;
use App\Shared\Infrastructure\Identifier\IntegerIdentifier;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final readonly class IntegerIdentifierNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public function __construct(
        private PropertyMetadataFactoryInterface $propertyMetadataFactory,
    ) {
    }

    /**
     * @param IntegerIdentifier $data
     */
    #[\Override]
    public function normalize(mixed $data, ?string $format = null, array $context = []): int
    {
        return (int) $data->__toString();
    }

    #[\Override]
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof IntegerIdentifier;
    }

    /**
     * @param int|string $data
     */
    #[\Override]
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): IntegerIdentifier
    {
        if (\is_string($data)) {
            if (!ctype_digit($data)) {
                throw NotNormalizableValueException::createForUnexpectedDataType(message: 'Invalid integer identifier.', data: $data, expectedTypes: ['integer'], path: $context['deserialization_path'] ?? null, useMessageForUser: true);
            }

            $data = (int) $data;
        }

        if (!\is_int($data)) {
            throw NotNormalizableValueException::createForUnexpectedDataType(message: 'Invalid integer identifier.', data: $data, expectedTypes: ['integer'], path: $context['deserialization_path'] ?? null, useMessageForUser: true);
        }

        return new IntegerIdentifier($data);
    }

    #[\Override]
    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        if (!is_a($type, Identifier::class, true)) {
            return false;
        }

        if (IntegerIdentifier::class === $type) {
            return true;
        }

        // cannot detect IntegerIdentifier because typehint is probably Identifier
        // detect IntegerIdentifier from metadata
        $propertyMetadata = $this->propertyMetadataFactory->create($context['operation']->getClass(), $context['deserialization_path'] ?? '');
        $schema = $propertyMetadata->getSchema();

        $schemaType = $schema['type'] ?? null;
        if (!$schemaType) {
            return false;
        }

        $schemaType = \is_array($schemaType) ? $schemaType : [$schemaType];

        return \in_array('integer', $schemaType, true);
    }

    #[\Override]
    public function getSupportedTypes(?string $format): array
    {
        return [
            // Don't cache as multiple Normalizer can support the Identifier interface.
            // The support depends on the propertyMetadata Schema.
            Identifier::class => false,
            IntegerIdentifier::class => true,
        ];
    }
}
