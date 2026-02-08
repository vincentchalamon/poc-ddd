<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Symfony\Serializer\Float;

use App\Shared\Domain\Exception\FloatValueException;
use App\Shared\Domain\Float\FloatValue;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Converts an int, float or string value to a {@see FloatValue}.
 */
final readonly class FloatValueNormalizer implements NormalizerInterface, DenormalizerInterface
{
    /**
     * @param FloatValue $data
     */
    #[\Override]
    public function normalize(mixed $data, ?string $format = null, array $context = []): float
    {
        return $data->toFloat(decimals: $context['decimals'] ?? null);
    }

    #[\Override]
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof FloatValue;
    }

    #[\Override]
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): FloatValue
    {
        if (!is_numeric($data)) {
            throw NotNormalizableValueException::createForUnexpectedDataType(message: 'Value is not a valid number.', data: $data, expectedTypes: ['int', 'float', 'string'], path: $context['deserialization_path'] ?? null, useMessageForUser: true);
        }

        try {
            return new FloatValue(value: $data);
        } catch (FloatValueException $floatValueException) {
            throw NotNormalizableValueException::createForUnexpectedDataType(message: $floatValueException->getMessage(), data: $data, expectedTypes: ['int', 'float', 'string'], path: $context['deserialization_path'] ?? null, useMessageForUser: true);
        }
    }

    #[\Override]
    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return FloatValue::class === $type;
    }

    #[\Override]
    public function getSupportedTypes(?string $format): array
    {
        return [
            FloatValue::class => true,
        ];
    }
}
