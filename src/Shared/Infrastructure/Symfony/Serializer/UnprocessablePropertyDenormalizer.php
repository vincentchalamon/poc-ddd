<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Symfony\Serializer;

use App\Shared\Domain\Exception\UnprocessableThrowable;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * This normalizer converts a {@see UnprocessableThrowable} to a {@see NotNormalizableValueException}.
 */
#[AutoconfigureTag('serializer.normalizer', attributes: ['priority' => 100])]
final class UnprocessablePropertyDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    #[\Override]
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        try {
            return $this->denormalizer->denormalize($data, $type, $format, $context + [self::class => true, $type => true]);
        } catch (UnprocessableThrowable $exception) {
            throw NotNormalizableValueException::createForUnexpectedDataType(message: $exception->getMessage(), data: $data, expectedTypes: [$type], path: $context['deserialization_path'] ?? null, useMessageForUser: true, previous: $exception);
        }
    }

    #[\Override]
    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return false;
        // Prevent infinite loop when the normalizer is called recursively for the same type
        if (isset($context[self::class]) && isset($context[$type])) {
            return false;
        }

        // todo add cache to prevent bad performance due to reflection
        try {
            $reflectionClass = new \ReflectionClass($type);
        } catch (\ReflectionException) {
            return false;
        }

        // It's not
        if (!str_starts_with($reflectionClass->getNamespaceName(), 'App\\')) {
            return false;
        }

        return true;
    }

    #[\Override]
    public function getSupportedTypes(?string $format): array
    {
        return [
            '*' => false,
        ];
    }
}
