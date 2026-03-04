<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Doctrine\ORM\Subscriber;

use App\Shared\Infrastructure\Doctrine\ORM\Mapping\NullableEmbedded;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PostLoadEventArgs;
use Doctrine\ORM\Events;
use Doctrine\Persistence\ObjectManager;

#[AsDoctrineListener(event: Events::postLoad)]
final readonly class NullableEmbeddableSubscriber
{
    public function postLoad(PostLoadEventArgs $args): void
    {
        $entity = $args->getObject();
        $objectManager = $args->getObjectManager();
        $classMetadata = $objectManager->getClassMetadata($entity::class);

        if (empty($classMetadata->embeddedClasses)) {
            return;
        }

        $reflectionClass = $classMetadata->getReflectionClass();

        foreach ($classMetadata->embeddedClasses as $property => $mapping) {
            if (!$reflectionClass->hasProperty($property)) {
                continue;
            }

            $reflectionProperty = $reflectionClass->getProperty($property);

            if (empty($reflectionProperty->getAttributes(NullableEmbedded::class))) {
                continue;
            }

            if (!$reflectionProperty->isInitialized($entity)) {
                continue;
            }

            $embeddableObject = $reflectionProperty->getValue($entity);

            if (null === $embeddableObject) {
                continue;
            }

            if ($this->isEmbeddableEmpty($objectManager, $embeddableObject)) {
                $reflectionProperty->setValue($entity, null);
            }
        }
    }

    private function isEmbeddableEmpty(ObjectManager $objectManager, object $embeddable): bool
    {
        $embeddableMetadata = $objectManager->getClassMetadata($embeddable::class);
        $reflectionEmbeddable = $embeddableMetadata->getReflectionClass();

        foreach ($embeddableMetadata->fieldMappings as $fieldName => $mapping) {
            if (!$reflectionEmbeddable->hasProperty($fieldName)) {
                continue;
            }

            $prop = $reflectionEmbeddable->getProperty($fieldName);

            if ($prop->isInitialized($embeddable) && null !== $prop->getValue($embeddable)) {
                return false;
            }
        }

        return true;
    }
}
