<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Symfony\Serializer\Text;

use App\Shared\Domain\Text\NonEmptyString;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class NonEmptyStringNormalizer implements NormalizerInterface, DenormalizerInterface
{
    /** @param NonEmptyString $data */
    #[\Override]
    public function normalize(mixed $data, ?string $format = null, array $context = []): string
    {
        return (string) $data;
    }

    #[\Override]
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): ?NonEmptyString
    {
        if (null === $data) {
            return null;
        }

        if (!\is_string($data)) {
            throw NotNormalizableValueException::createForUnexpectedDataType(message: 'Value is not a valid string.', data: $data, expectedTypes: ['string'], path: $context['deserialization_path'] ?? null, useMessageForUser: true);
        }

        return new NonEmptyString($data);
    }

    #[\Override]
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof NonEmptyString;
    }

    #[\Override]
    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return NonEmptyString::class === $type;
    }

    #[\Override]
    public function getSupportedTypes(?string $format): array
    {
        return [
            NonEmptyString::class => true,
        ];
    }
}
