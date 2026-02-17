<?php

declare(strict_types=1);

namespace App\Drawer\Infrastructure\Symfony\Serializer;

use App\Drawer\Domain\Model\EmailAddress;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final readonly class EmailAddressNormalizer implements NormalizerInterface, DenormalizerInterface
{
    /** @param EmailAddress $data */
    #[\Override]
    public function normalize(mixed $data, ?string $format = null, array $context = []): string
    {
        return (string) $data;
    }

    #[\Override]
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): ?EmailAddress
    {
        if (null === $data) {
            return null;
        }

        if (!\is_string($data)) {
            throw NotNormalizableValueException::createForUnexpectedDataType(message: 'Value is not a valid string.', data: $data, expectedTypes: ['string'], path: $context['deserialization_path'] ?? null, useMessageForUser: true);
        }

        return new EmailAddress($data);
    }

    #[\Override]
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof EmailAddress;
    }

    #[\Override]
    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return EmailAddress::class === $type;
    }

    #[\Override]
    public function getSupportedTypes(?string $format): array
    {
        return [
            EmailAddress::class => false,
        ];
    }
}
