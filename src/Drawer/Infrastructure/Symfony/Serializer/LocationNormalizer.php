<?php

declare(strict_types=1);

namespace App\Drawer\Infrastructure\Symfony\Serializer;

use App\Drawer\Domain\Model\Location;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final readonly class LocationNormalizer implements NormalizerInterface, DenormalizerInterface
{
    /** @param Location $data */
    #[\Override]
    public function normalize(mixed $data, ?string $format = null, array $context = []): array
    {
        return [
            'latitude' => $data->latitude(),
            'longitude' => $data->longitude(),
        ];
    }

    #[\Override]
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): ?Location
    {
        if (null === $data) {
            return null;
        }

        if (!\is_array($data)) {
            throw NotNormalizableValueException::createForUnexpectedDataType(message: 'Value is not a valid array.', data: $data, expectedTypes: ['array'], path: $context['deserialization_path'] ?? null, useMessageForUser: true);
        }

        return new Location(...$data);
    }

    #[\Override]
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Location;
    }

    #[\Override]
    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return Location::class === $type;
    }

    #[\Override]
    public function getSupportedTypes(?string $format): array
    {
        return [
            Location::class => false,
        ];
    }
}
