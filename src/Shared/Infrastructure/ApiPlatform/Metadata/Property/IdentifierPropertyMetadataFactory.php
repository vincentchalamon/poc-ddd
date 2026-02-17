<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\ApiPlatform\Metadata\Property;

use ApiPlatform\JsonSchema\Metadata\Property\Factory\SchemaPropertyMetadataFactory;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\Exception\PropertyNotFoundException;
use ApiPlatform\Metadata\Property\Factory\PropertyMetadataFactoryInterface;
use App\Shared\Domain\Identifier\Identifier;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\TypeInfo\Type\ObjectType;

#[AsDecorator(decorates: 'api_platform.metadata.property.metadata_factory', priority: 11)]
final readonly class IdentifierPropertyMetadataFactory implements PropertyMetadataFactoryInterface
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

        if (!is_a($type->getClassName(), Identifier::class, true)) {
            return $propertyMetadata;
        }

        $types = $propertyMetadata->getTypes() ?? ['https://schema.org/identifier'];
        $schema = $propertyMetadata->getSchema() ?? [];

        if (null === $propertyMetadata->isIdentifier()) {
            $propertyMetadata = $propertyMetadata->withIdentifier(true);
        }

        // Default format to uuid
        if (!isset($schema['type'])) {
            $schema['type'] = $type->isNullable() ? ['string', 'null'] : 'string';
            $schema['format'] = 'uuid';
        }

        if (!isset($schema['readOnly']) && $propertyMetadata->isIdentifier()) {
            $schema['readOnly'] = true;
        }

        if (!isset($schema['externalDocs'])) {
            $schema['externalDocs'] = ['url' => $propertyMetadata->getTypes()[0] ?? 'https://schema.org/identifier'];
        }

        return $propertyMetadata
            ->withTypes($types)
            ->withSchema($schema)
            // prevents API Platform to modify this schema
            ->withExtraProperties($propertyMetadata->getExtraProperties() + [SchemaPropertyMetadataFactory::JSON_SCHEMA_USER_DEFINED => true]);
    }
}
