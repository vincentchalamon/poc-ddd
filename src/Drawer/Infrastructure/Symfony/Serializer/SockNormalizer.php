<?php

declare(strict_types=1);

namespace App\Drawer\Infrastructure\Symfony\Serializer;

use App\Drawer\Domain\Factory\NameFactory;
use App\Drawer\Domain\Model\Sock;
use App\Shared\Infrastructure\Identifier\UuidIdentifier;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\PropertyInfo\PropertyInfoExtractorInterface;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Exception\PartialDenormalizationException;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\TypeInfo\Type\ObjectType;

/**
 * This normalizer is responsible for normalizing and denormalizing the {@see Sock} object.
 *
 * It uses a custom {@see PropertyInfoExtractor} to get the private properties of the Sock class and their types,
 * and then uses the normalizer and denormalizer to normalize and denormalize the properties accordingly.
 *
 * This is not a good approach in general, as it recreates the behavior of the Symfony Serializer.
 * However, it is necessary in this case because the Sock class has private properties and no public getters or setters,
 * and we want to keep the Sock Rich Model out of the framework's constraints.
 *
 * This demonstrates the complexity necessary to keep the domain model clean and decoupled from the framework
 * (except for the attributes), and the trade-offs involved in doing so.
 */
final class SockNormalizer implements DenormalizerInterface, DenormalizerAwareInterface, NormalizerInterface, NormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    public function __construct(
        #[Autowire(service: 'app.drawer.sock_property_info')]
        private readonly PropertyInfoExtractorInterface $propertyInfoExtractor,
        private readonly NameFactory $nameFactory,
    ) {
    }

    /**
     * @param Sock                                     $data
     * @param array{groups?: array<array-key, string>} $context
     */
    #[\Override]
    public function normalize(mixed $data, ?string $format = null, array $context = []): array
    {
        $normalizedData = [];
        foreach ($this->propertyInfoExtractor->getProperties(Sock::class, $context + ['serializer_groups' => $context['groups'] ?? []]) as $property) {
            $normalizedData[$property] = $this->normalizer->normalize($data->{$property}(), $format, $context + [self::class => true]);
        }

        return $normalizedData;
    }

    #[\Override]
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): Sock
    {
        $errors = [];
        $properties = $this->propertyInfoExtractor->getProperties($type, $context);
        foreach ($properties as $property) {
            $value = $data[$property] ?? null;

            /** @var ObjectType|null $propertyType */
            $propertyType = $this->propertyInfoExtractor->getType($type, $property, $context);
            if (isset($data[$property])) {
                try {
                    // Denormalize ValueObject
                    $data[$property] = $this->denormalizer->denormalize($value, $propertyType?->getClassName() ?? 'string', $format, $context + ['deserialization_path' => $property]);
                } catch (NotNormalizableValueException $exception) {
                    if (!isset($context['not_normalizable_value_exceptions'])) {
                        throw $exception;
                    }

                    $errors[] = $exception;
                }
                continue;
            }

            // System data
            if ('identifier' === $property) {
                $data['identifier'] = new UuidIdentifier();
                continue;
            }
            if ('name' === $property) {
                $data['name'] = $this->nameFactory->create();
                continue;
            }

            if (!$propertyType->isNullable()) {
                // Check for missing property
                $errors[] = NotNormalizableValueException::createForUnexpectedDataType(message: 'This value should not be null.', data: null, expectedTypes: [$propertyType->getClassName() ?? 'string'], path: $property, useMessageForUser: true);
            }
        }

        if ($errors) {
            // This is converted to ConstraintViolationList and ValidationException by DeserializeProvider
            throw new PartialDenormalizationException($data, $errors);
        }

        return new Sock(...$data);
    }

    #[\Override]
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Sock && !isset($context[self::class]);
    }

    #[\Override]
    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return Sock::class === $type;
    }

    #[\Override]
    public function getSupportedTypes(?string $format): array
    {
        return [
            Sock::class => false,
        ];
    }
}
