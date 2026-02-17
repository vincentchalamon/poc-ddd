<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\ApiPlatform\Metadata\Property;

use ApiPlatform\JsonSchema\Metadata\Property\Factory\SchemaPropertyMetadataFactory;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\Exception\PropertyNotFoundException;
use ApiPlatform\Metadata\Property\Factory\PropertyMetadataFactoryInterface;
use App\Shared\Domain\Number\FloatValue;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\TypeInfo\Type\ObjectType;

/**
 * It is recommended to set "multipleOf", "minimum" and "maximum" JSON Schema keys.
 *
 * @see https://json-schema.org/understanding-json-schema/reference/numeric#range
 */
#[AsDecorator(decorates: 'api_platform.metadata.property.metadata_factory', priority: 11)]
final readonly class FloatValuePropertyMetadataFactory implements PropertyMetadataFactoryInterface
{
    public function __construct(
        private ?PropertyMetadataFactoryInterface $decorated = null,
    ) {
    }

    #[\Override]
    public function create(string $resourceClass, string $property, array $options = []): ApiProperty
    {
        if (!$this->decorated instanceof PropertyMetadataFactoryInterface) {
            $propertyMetadata = new ApiProperty();
        } else {
            try {
                $propertyMetadata = $this->decorated->create($resourceClass, $property, $options);
            } catch (PropertyNotFoundException) {
                $propertyMetadata = new ApiProperty();
            }
        }

        $type = $propertyMetadata->getNativeType();
        if (!$type instanceof ObjectType) {
            return $propertyMetadata;
        }

        if (FloatValue::class === $type->getClassName()) {
            return $propertyMetadata;
        }

        return $propertyMetadata
            ->withSchema(($propertyMetadata->getSchema() ?? []) + [
                'type' => $type->isNullable() ? ['number', 'string', 'null'] : ['number', 'string'],
            ])
            // prevents API Platform to detect it as an object
            ->withExtraProperties($propertyMetadata->getExtraProperties() + [SchemaPropertyMetadataFactory::JSON_SCHEMA_USER_DEFINED => true]);
    }
}
