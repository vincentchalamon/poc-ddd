<?php

declare(strict_types=1);

namespace App\Drawer\Infrastructure\Symfony\Serializer;

use App\Drawer\Domain\Model\Sock;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\PropertyInfo\PropertyInfoExtractorInterface;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
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
        $objectToPopulate = $context['object_to_populate'] ?? null;
        if (!$objectToPopulate instanceof Sock) {
            throw NotNormalizableValueException::createForUnexpectedDataType(message: 'Cannot denormalize Sock without an object to populate.', data: $objectToPopulate, expectedTypes: [Sock::class], path: $context['deserialization_path'] ?? null);
        }

        foreach ($data as $property => $value) {
            $propertyType = $this->propertyInfoExtractor->getType($type, $property, $context);
            if (!$propertyType instanceof ObjectType) {
                continue;
            }
            $data[$property] = $this->denormalizer->denormalize($value, $propertyType->getClassName(), $format, $context);
        }

        $properties = $this->propertyInfoExtractor->getProperties($type, $context);
        foreach ($properties as $property) {
            if (!\array_key_exists($property, $data)) {
                $data[$property] = $objectToPopulate->{$property}();
            }
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
