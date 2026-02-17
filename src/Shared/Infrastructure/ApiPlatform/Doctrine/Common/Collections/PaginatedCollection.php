<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\ApiPlatform\Doctrine\Common\Collections;

use ApiPlatform\State\Pagination\PaginatorInterface;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\ReadableCollection as ReadableCollectionInterface;
use Doctrine\Common\Collections\Selectable as SelectableInterface;

/**
 * Collection decorator with pagination, compatible with API Platform {@see PaginatorInterface}.
 *
 * <code>
 * new PaginatedCollection(
 *     $collection,
 *     $context['filters']['page'] ?? 1,
 *     $context['filters']['itemsPerPage'] ?? 30
 * );
 * </code>
 *
 * @template TKey of array-key
 * @template T of object
 *
 * @implements ReadableCollectionInterface<TKey, T>
 * @implements SelectableInterface<TKey, T>
 * @implements PaginatorInterface<T>
 */
final readonly class PaginatedCollection implements ReadableCollectionInterface, SelectableInterface, PaginatorInterface
{
    /**
     * @param ReadableCollectionInterface<TKey, T> $collection
     */
    public function __construct(
        private ReadableCollectionInterface $collection,
        private int $page,
        private int $itemsPerPage,
    ) {
    }

    /**
     * @template U of object
     *
     * @param \Closure(T):U $func
     *
     * @return ReadableCollectionInterface<TKey, U>
     */
    #[\Override]
    public function map(\Closure $func): ReadableCollectionInterface
    {
        return new self(
            collection: $this->collection->map($func),
            page: $this->page,
            itemsPerPage: $this->itemsPerPage,
        );
    }

    /**
     * @return self<TKey, T>
     */
    #[\Override]
    public function matching(Criteria $criteria): self
    {
        if (!$this->collection instanceof SelectableInterface) {
            throw new \InvalidArgumentException(\sprintf('Cannot call "matching" method on %s.', $this->collection::class));
        }

        // If the collection is a PersistentCollection or QueryBuilderCollection,
        // the collection should not be initialized
        /** @var ReadableCollectionInterface<TKey, T> $matchedData */
        $matchedData = $this->collection->matching($criteria);

        return new self(
            collection: $matchedData,
            page: $this->page,
            itemsPerPage: $this->itemsPerPage,
        );
    }

    /**
     * @return self<TKey, T>
     */
    #[\Override]
    public function filter(\Closure $p): self
    {
        // filtering would cause the PaginatedCollection Value Object to become invalid
        throw new \LogicException('A PaginatedCollection cannot be filtered as the pagination would be invalid.');
    }

    /**
     * @return self<TKey, T>
     */
    #[\Override]
    public function reduce(\Closure $func, mixed $initial = null): self
    {
        // reducing would cause the PaginatedCollection Value Object to become invalid
        throw new \LogicException('A PaginatedCollection cannot be reduced as the pagination would be invalid.');
    }

    #[\Override]
    public function partition(\Closure $p): array
    {
        return $this->collection->partition($p);
    }

    /**
     * @return \Traversable<TKey, T>
     */
    #[\Override]
    public function getIterator(): \Traversable
    {
        return $this->collection->getIterator();
    }

    #[\Override]
    public function count(): int
    {
        return $this->collection->count();
    }

    /**
     * @param TMaybeContained $element the element to search for
     *
     * @return (TMaybeContained is T ? bool : false) TRUE if the collection contains the element, FALSE otherwise
     *
     * @template TMaybeContained
     */
    #[\Override]
    public function contains(mixed $element): bool
    {
        return $this->collection->contains($element);
    }

    #[\Override]
    public function isEmpty(): bool
    {
        return $this->collection->isEmpty();
    }

    /**
     * @param TKey $key the key/index to check for
     */
    #[\Override]
    public function containsKey(int|string $key): bool
    {
        return $this->collection->containsKey($key);
    }

    /**
     * @param TKey $key the key/index of the element to retrieve
     *
     * @return T|null
     */
    #[\Override]
    public function get(int|string $key): mixed
    {
        return $this->collection->get($key);
    }

    #[\Override]
    public function getKeys(): array
    {
        return $this->collection->getKeys();
    }

    #[\Override]
    public function getValues(): array
    {
        return $this->collection->getValues();
    }

    #[\Override]
    public function toArray(): array
    {
        return $this->collection->toArray();
    }

    #[\Override]
    public function first(): mixed
    {
        return $this->collection->first();
    }

    #[\Override]
    public function last(): mixed
    {
        return $this->collection->last();
    }

    #[\Override]
    public function key(): int|string|null
    {
        return $this->collection->key();
    }

    #[\Override]
    public function current(): mixed
    {
        return $this->collection->current();
    }

    #[\Override]
    public function next(): mixed
    {
        return $this->collection->next();
    }

    #[\Override]
    public function slice(int $offset, ?int $length = null): array
    {
        return $this->collection->slice($offset, $length);
    }

    #[\Override]
    public function exists(\Closure $p): bool
    {
        return $this->collection->exists($p);
    }

    #[\Override]
    public function forAll(\Closure $p): bool
    {
        return $this->collection->forAll($p);
    }

    /**
     * @param TMaybeContained $element the element to search for
     *
     * @return (TMaybeContained is T ? TKey|false : false) the key/index of the element or FALSE if the element was not found
     *
     * @template TMaybeContained
     */
    #[\Override]
    public function indexOf(mixed $element)
    {
        return $this->collection->indexOf($element);
    }

    #[\Override]
    public function findFirst(\Closure $p): mixed
    {
        return $this->collection->findFirst($p);
    }

    #[\Override]
    public function getLastPage(): float
    {
        if ($this->itemsPerPage <= 0) {
            return 1;
        }

        return ceil($this->count() / $this->itemsPerPage) ?: 1;
    }

    #[\Override]
    public function getTotalItems(): float
    {
        return $this->count();
    }

    #[\Override]
    public function getCurrentPage(): float
    {
        return (float) $this->page;
    }

    #[\Override]
    public function getItemsPerPage(): float
    {
        return (float) $this->itemsPerPage;
    }
}
