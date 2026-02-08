<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Doctrine\Common\Collections;

use Doctrine\Common\Collections\Collection as CollectionInterface;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\ReadableCollection as ReadableCollectionInterface;
use Doctrine\Common\Collections\Selectable as SelectableInterface;

/**
 * Collection decorator with lazy mapper, compatible with {@see PersistentCollection}.
 * Highly inspired by {@see AbstractLazyCollection}.
 *
 * Using a {@see PersistentCollection} with an extra_lazy association run database queries
 * on methods like "count", "slice"...
 *
 * If the mapper is applied to soon (e.g.: before pagination), the {@see PersistentCollection}
 * is initialized with full data, which may cause performance issues in case of big collections.
 *
 * This collection applies the mapper properly, preserving {@see PersistentCollection} features.
 *
 * <code>
 * new MappedCollection(
 *     $collection,
 *     static fn (object $object) => $this->objectMapper->map($object, DTO::class),
 * );
 * </code>
 *
 * @template TKey of array-key
 * @template T of object
 * @template DTO of object
 *
 * @template-implements CollectionInterface<TKey, DTO>
 * @template-implements SelectableInterface<TKey, DTO>
 */
final readonly class MappedCollection implements CollectionInterface, SelectableInterface
{
    /**
     * @param ReadableCollectionInterface<TKey, T>|CollectionInterface<TKey, T> $collection
     * @param \Closure(T): DTO                                                  $mapper
     */
    public function __construct(
        private ReadableCollectionInterface|CollectionInterface $collection,
        private \Closure $mapper,
    ) {
    }

    /**
     * @param DTO $element
     */
    #[\Override]
    public function contains(mixed $element): bool
    {
        // Collection::map initializes the PersistentCollection and QueryBuilderCollection
        // which loads the full data and disables their benefits in case of PaginatedCollection,
        // but it's necessary in this case
        return $this->collection->map($this->mapper)->contains($element);
    }

    #[\Override]
    public function isEmpty(): bool
    {
        return $this->collection->isEmpty();
    }

    /**
     * @param TKey $key
     */
    #[\Override]
    public function containsKey(mixed $key): bool
    {
        return $this->collection->containsKey($key);
    }

    /**
     * @param TKey $key
     *
     * @return DTO
     */
    #[\Override]
    public function get(string|int $key): mixed
    {
        return ($this->mapper)($this->collection->get($key));
    }

    /**
     * @return list<TKey>
     */
    #[\Override]
    public function getKeys(): iterable
    {
        return $this->collection->getKeys();
    }

    /**
     * @return list<DTO>
     */
    #[\Override]
    public function getValues(): iterable
    {
        return array_map($this->mapper, $this->collection->getValues());
    }

    /**
     * @return array<DTO>
     */
    #[\Override]
    public function toArray(): array
    {
        return array_map($this->mapper, $this->collection->toArray());
    }

    /**
     * @return DTO
     */
    #[\Override]
    public function first()
    {
        return ($this->mapper)($this->collection->first());
    }

    /**
     * @return DTO
     */
    #[\Override]
    public function last()
    {
        return ($this->mapper)($this->collection->last());
    }

    /**
     * @return TKey
     */
    #[\Override]
    public function key()
    {
        return $this->collection->key();
    }

    /**
     * @return DTO
     */
    #[\Override]
    public function current()
    {
        return ($this->mapper)($this->collection->current());
    }

    /**
     * @return DTO
     */
    #[\Override]
    public function next()
    {
        return ($this->mapper)($this->collection->next());
    }

    /**
     * @return list<DTO>
     */
    #[\Override]
    public function slice(int $offset, ?int $length = null): iterable
    {
        // Do not call Collection::map before Collection::getIterator
        // as it initializes the PersistentCollection and QueryBuilderCollection
        // and disables their benefits in case of PaginatedCollection
        return array_map($this->mapper, $this->collection->slice($offset, $length));
    }

    /**
     * @param \Closure(TKey, DTO): bool $p
     */
    #[\Override]
    public function exists(\Closure $p): bool
    {
        // Collection::map initializes the PersistentCollection and QueryBuilderCollection
        // which loads the full data and disables their benefits in case of PaginatedCollection,
        // but it's necessary in this case
        return $this->collection->map($this->mapper)->exists($p);
    }

    /**
     * This method doesn't return a MappedCollection but the original collection.
     *
     * @param \Closure(DTO, TKey): bool $p
     *
     * @return ReadableCollectionInterface<TKey, DTO>|CollectionInterface<TKey, DTO>
     */
    #[\Override]
    public function filter(\Closure $p): ReadableCollectionInterface|CollectionInterface
    {
        // Collection::map initializes the PersistentCollection and QueryBuilderCollection
        // which loads the full data and disables their benefits in case of PaginatedCollection,
        // but it's necessary in this case
        return $this->collection->map($this->mapper)->filter($p);
    }

    /**
     * @template V of object
     *
     * @param \Closure(DTO): V $func
     *
     * @return self<TKey, DTO, V>
     */
    #[\Override]
    public function map(\Closure $func): self
    {
        /*
         * Can't replace the mapper as it would receive invalid data.
         *
         * For instance, let's consider the following MappedCollection,
         * mapping a collection of Doctrine Entities to Domain DTO objects:
         * <code>
         * $collection = new MappedCollection(
         *     collection: PersistentCollection<FooEntity>,
         *     mapper: fn (FooEntity $entity): Foo => new Foo(...),
         * );
         * </code>
         *
         * Calling MappedCollection::map, for instance in a "toEntity" mapper,
         * to convert the collection of Domain DTO objects to Doctrine Entities:
         * <code>
         * $collection->map(fn (Foo $object): FooEntity => new FooEntity(...));
         * </code>
         *
         * Replacing the mapper of the original MappedCollection would call the
         * new mapper (which expects a Domain DTO object) with the original collection
         * data (a Doctrine Entity).
         *
         * The MappedCollection MUST encapsulate the MappedCollection.
         *
         * This would result to something like:
         * <code>
         * $collection = new MappedCollection(
         *     collection: new MappedCollection(
         *         collection: PersistentCollection<FooEntity>,
         *         mapper: fn (FooEntity $entity): Foo => new Foo(...),
         *     ),
         *     mapper: fn (Foo $entity): FooEntity => new FooEntity(...),
         * );
         * </code>
         */
        return new self(
            collection: $this,
            mapper: $func,
        );
    }

    /**
     * This method doesn't return an array of MappedCollections but the original collections.
     *
     * @param \Closure(TKey, DTO): bool $p
     *
     * @return array{0: ReadableCollectionInterface<TKey, DTO>|CollectionInterface<TKey, DTO>, 1: ReadableCollectionInterface<TKey, DTO>|CollectionInterface<TKey, DTO>}
     */
    #[\Override]
    public function partition(\Closure $p): array
    {
        // Collection::map initializes the PersistentCollection and QueryBuilderCollection
        // which loads the full data and disables their benefits in case of PaginatedCollection,
        // but it's necessary in this case
        return $this->collection->map($this->mapper)->partition($p);
    }

    /**
     * @param \Closure(TKey, DTO): bool $p
     */
    #[\Override]
    public function forAll(\Closure $p): bool
    {
        // Collection::map initializes the PersistentCollection and QueryBuilderCollection
        // which loads the full data and disables their benefits in case of PaginatedCollection,
        // but it's necessary in this case
        return $this->collection->map($this->mapper)->forAll($p);
    }

    /**
     * @param DTO $element
     *
     * @return TKey
     */
    #[\Override]
    public function indexOf(mixed $element)
    {
        // Collection::map initializes the PersistentCollection and QueryBuilderCollection
        // which loads the full data and disables their benefits in case of PaginatedCollection,
        // but it's necessary in this case
        return $this->collection->map($this->mapper)->indexOf($element);
    }

    /**
     * @param \Closure(TKey, DTO): bool $p
     *
     * @return DTO|null
     *
     * @phpstan-ignore-next-line return.unusedType ArrayCollection may return null
     */
    #[\Override]
    public function findFirst(\Closure $p)
    {
        return ($this->mapper)($this->collection->findFirst($p));
    }

    /**
     * @param \Closure(T, T): T $func
     */
    #[\Override]
    public function reduce(\Closure $func, mixed $initial = null)
    {
        return ($this->mapper)($this->collection->reduce($func, $initial));
    }

    #[\Override]
    public function count(): int
    {
        return $this->collection->count();
    }

    /**
     * @return \Traversable<TKey, DTO>
     */
    #[\Override]
    public function getIterator(): \Traversable
    {
        // Do not call Collection::map before Collection::getIterator
        // as it initializes the PersistentCollection and QueryBuilderCollection
        // and disables their benefits in case of PaginatedCollection
        return new \ArrayIterator(array_map($this->mapper, iterator_to_array($this->collection->getIterator())));
    }

    /**
     * @return self<TKey, T, DTO>
     */
    #[\Override]
    public function matching(Criteria $criteria): self
    {
        if (!$this->collection instanceof SelectableInterface) {
            throw new \InvalidArgumentException(\sprintf('Cannot call "matching" method on %s.', $this->collection::class));
        }

        // If the collection is a PersistentCollection or QueryBuilderCollection,
        // the collection should not be initialized
        // which keep their benefits in case of PaginatedCollection
        return new self(
            collection: $this->collection->matching($criteria),
            mapper: $this->mapper,
        );
    }

    /**
     * @param TKey $offset
     */
    #[\Override]
    public function offsetExists(mixed $offset): bool
    {
        if (!$this->collection instanceof CollectionInterface) {
            throw new \InvalidArgumentException(\sprintf('%s does not implement %s.', $this->collection::class, CollectionInterface::class));
        }

        return $this->collection->offsetExists($offset);
    }

    /**
     * @param TKey $offset
     *
     * @return DTO
     */
    #[\Override]
    public function offsetGet(mixed $offset): mixed
    {
        if (!$this->collection instanceof CollectionInterface) {
            throw new \InvalidArgumentException(\sprintf('%s does not implement %s.', $this->collection::class, CollectionInterface::class));
        }

        return ($this->mapper)($this->collection->offsetGet($offset));
    }

    /**
     * @param T $element
     */
    #[\Override]
    public function add(mixed $element): void
    {
        if (!$this->collection instanceof CollectionInterface) {
            throw new \InvalidArgumentException(\sprintf('%s does not implement %s.', $this->collection::class, CollectionInterface::class));
        }

        // No need to run mapper on object as the original collection should be of the same type
        $this->collection->add($element);

        // PersistentCollection::add return type is not valid according to Collection::add
    }

    /**
     * @param TKey $key
     * @param T    $value
     */
    #[\Override]
    public function set(int|string $key, mixed $value): void
    {
        if (!$this->collection instanceof CollectionInterface) {
            throw new \InvalidArgumentException(\sprintf('%s does not implement %s.', $this->collection::class, CollectionInterface::class));
        }

        // No need to run mapper on object as the original collection should be of the same type
        $this->collection->set($key, $value);
    }

    /**
     * @param TKey $offset
     * @param T    $value
     */
    #[\Override]
    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (!$this->collection instanceof CollectionInterface) {
            throw new \InvalidArgumentException(\sprintf('%s does not implement %s.', $this->collection::class, CollectionInterface::class));
        }

        // No need to run mapper on object as the original collection should be of the same type
        $this->collection->offsetSet($offset, $value);
    }

    #[\Override]
    public function clear(): void
    {
        if (!$this->collection instanceof CollectionInterface) {
            throw new \InvalidArgumentException(\sprintf('%s does not implement %s.', $this->collection::class, CollectionInterface::class));
        }

        $this->collection->clear();
    }

    /**
     * @param TKey $key
     *
     * @return DTO
     */
    #[\Override]
    public function remove(int|string $key)
    {
        if (!$this->collection instanceof CollectionInterface) {
            throw new \InvalidArgumentException(\sprintf('%s does not implement %s.', $this->collection::class, CollectionInterface::class));
        }

        return ($this->mapper)($this->collection->remove($key));
    }

    /**
     * @param T $element
     */
    #[\Override]
    public function removeElement(mixed $element): bool
    {
        if (!$this->collection instanceof CollectionInterface) {
            throw new \InvalidArgumentException(\sprintf('%s does not implement %s.', $this->collection::class, CollectionInterface::class));
        }

        // No need to run mapper on object as the original collection should be of the same type
        return $this->collection->removeElement($element);
    }

    /**
     * @param TKey $offset
     */
    #[\Override]
    public function offsetUnset(mixed $offset): void
    {
        if (!$this->collection instanceof CollectionInterface) {
            throw new \InvalidArgumentException(\sprintf('%s does not implement %s.', $this->collection::class, CollectionInterface::class));
        }

        $this->collection->offsetUnset($offset);
    }
}
