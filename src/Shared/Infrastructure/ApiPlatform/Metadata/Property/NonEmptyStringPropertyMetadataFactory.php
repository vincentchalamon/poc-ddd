<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\ApiPlatform\Metadata\Property;

use ApiPlatform\JsonSchema\Metadata\Property\Factory\SchemaPropertyMetadataFactory;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\Exception\PropertyNotFoundException;
use ApiPlatform\Metadata\Property\Factory\PropertyMetadataFactoryInterface;
use App\Shared\Domain\Text\NonEmptyString;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\TypeInfo\Type\ObjectType;

#[AsDecorator(decorates: 'api_platform.metadata.property.metadata_factory', priority: 11)]
final readonly class NonEmptyStringPropertyMetadataFactory implements PropertyMetadataFactoryInterface
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

        /** @var ObjectType<NonEmptyString>[] $types */
        $types = $propertyMetadata->getBuiltinTypes() ?? [];

        foreach ($types as $type) {
            if (NonEmptyString::class === $type->getClassName()) {
                continue;
            }

            $propertyMetadata = $propertyMetadata
                ->withSchema(($propertyMetadata->getSchema() ?? []) + [
                    'type' => $type->isNullable() ? ['string', 'null'] : 'string',
                    'minLength' => NonEmptyString::MINIMUM_LENGTH,
                ])
                // prevents API Platform to detect it as an object
                ->withExtraProperties($propertyMetadata->getExtraProperties() + [SchemaPropertyMetadataFactory::JSON_SCHEMA_USER_DEFINED => true]);
        }

        return $propertyMetadata;
    }
}
