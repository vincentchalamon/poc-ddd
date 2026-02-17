<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Doctrine\Common\Collections;

use Doctrine\Common\Collections\ReadableCollection as ReadableCollectionInterface;

/**
 * Read-only implementation of Doctrine's ArrayCollection with an immutable API.
 *
 * This class provides a minimal, immutable collection that mirrors Doctrine's ArrayCollection
 * behavior while preventing modifications.
 *
 * <code>
 * // Create from an array
 * new ReadableCollection([$entity1, $entity2]);
 * </code>
 *
 * <code>
 * // Create from a Doctrine collection
 * new ReadableCollection($collection);
 * </code>
 *
 * @template TKey of array-key
 * @template T of object
 *
 * @template-implements ReadableCollectionInterface<TKey, T>
 */
final class ReadableCollection implements ReadableCollectionInterface
{
    /**
     * @var array<TKey, T>
     */
    private array $collection;

    /**
     * @param array<TKey, T>|ReadableCollectionInterface<TKey, T> $collection
     */
    public function __construct(
        array|ReadableCollectionInterface $collection,
    ) {
        $this->collection = $collection instanceof ReadableCollectionInterface ? $collection->toArray() : $collection;
    }

    /**
     * @param \Closure(T $a, T $b): int $p
     *
     * @return self<TKey, T>
     */
    public function sort(\Closure $p): self
    {
        $collection = $this->collection;
        usort($collection, $p);

        return new self($collection);
    }

    #[\Override]
    public function count(): int
    {
        return \count($this->collection);
    }

    /**
     * @return \ArrayIterator<TKey, T>
     */
    #[\Override]
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->collection);
    }

    /**
     * @param TKey $offset
     */
    public function offsetExists(mixed $offset): bool
    {
        return $this->containsKey($offset);
    }

    #[\Override]
    public function isEmpty(): bool
    {
        return [] === $this->collection;
    }

    #[\Override]
    public function contains(mixed $element): bool
    {
        return \in_array($element, $this->collection, true);
    }

    #[\Override]
    public function containsKey(int|string $key): bool
    {
        return isset($this->collection[$key]) || \array_key_exists($key, $this->collection);
    }

    #[\Override]
    public function get(int|string $key)
    {
        return $this->collection[$key] ?? null;
    }

    #[\Override]
    public function getKeys(): array
    {
        return array_keys($this->collection);
    }

    #[\Override]
    public function getValues(): array
    {
        return array_values($this->collection);
    }

    #[\Override]
    public function toArray()
    {
        return $this->collection;
    }

    #[\Override]
    public function first()
    {
        return reset($this->collection);
    }

    #[\Override]
    public function last()
    {
        return end($this->collection);
    }

    #[\Override]
    public function key()
    {
        return key($this->collection);
    }

    #[\Override]
    public function current()
    {
        return current($this->collection);
    }

    #[\Override]
    public function next()
    {
        return next($this->collection);
    }

    #[\Override]
    public function slice(int $offset, ?int $length = null): array
    {
        return \array_slice($this->collection, $offset, $length, true);
    }

    /**
     * @param \Closure(TKey $key, T $value): bool $p
     */
    #[\Override]
    public function exists(\Closure $p): bool
    {
        return array_any($this->collection, fn ($element, $key) => $p($key, $element));
    }

    /**
     * @param \Closure(T $value, TKey $key): bool $p
     *
     * @return ReadableCollection<TKey, T>
     */
    #[\Override]
    public function filter(\Closure $p): self
    {
        return new self(array_filter($this->collection, $p, \ARRAY_FILTER_USE_BOTH));
    }

    /**
     * @template U of object
     *
     * @param \Closure(T): U $func
     *
     * @return ReadableCollection<TKey, U>
     */
    #[\Override]
    public function map(\Closure $func): self
    {
        return new self(array_map($func, $this->collection));
    }

    /**
     * @param \Closure(TKey $key, T $value): bool $p
     *
     * @return array{self<TKey, T>, self<TKey, T>}
     */
    #[\Override]
    public function partition(\Closure $p): array
    {
        $matches = [];
        $noMatches = [];
        foreach ($this->collection as $key => $element) {
            if ($p($key, $element)) {
                $matches[$key] = $element;
            } else {
                $noMatches[$key] = $element;
            }
        }

        return [new self($matches), new self($noMatches)];
    }

    /**
     * @param \Closure(TKey $key, T $value): bool $p
     */
    #[\Override]
    public function forAll(\Closure $p): bool
    {
        return array_all($this->collection, fn ($element, $key) => $p($key, $element));
    }

    /**
     * @param T $element
     *
     * @return TKey|false
     */
    #[\Override]
    public function indexOf(mixed $element)
    {
        return array_search($element, $this->collection, true);
    }

    /**
     * @param \Closure(TKey $key, T $value): bool $p
     *
     * @return T|null
     */
    #[\Override]
    public function findFirst(\Closure $p)
    {
        foreach ($this->collection as $key => $element) {
            if ($p($key, $element)) {
                return $element;
            }
        }

        return null;
    }

    /**
     * @template TInitial of mixed
     *
     * @param \Closure(T $carry, T $item): ?T $func
     * @param TInitial                        $initial
     *
     * @return T|TInitial
     */
    #[\Override]
    public function reduce(\Closure $func, $initial = null)
    {
        return array_reduce($this->collection, $func, $initial);
    }
}
