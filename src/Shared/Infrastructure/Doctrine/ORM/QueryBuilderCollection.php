<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Doctrine\ORM;

use App\Shared\Infrastructure\Doctrine\Common\Collections\ReadableCollection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Comparison;
use Doctrine\Common\Collections\Expr\Value;
use Doctrine\Common\Collections\ReadableCollection as ReadableCollectionInterface;
use Doctrine\Common\Collections\Selectable as SelectableInterface;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\Mapping\ClassMetadata;

/**
 * Collection decorator with {@see QueryBuilder} compatibility.
 * Highly inspired by {@see PersistentCollection}.
 *
 * <code>
 * new QueryBuilderCollection($queryBuilder);
 * </code>
 *
 * This collection is not meant to be used alone, but rather as a collection in a decoration flow.
 * For example:
 *
 * <code>
 * // The mapping of the collection is executed when the collection is parsed
 * new MappedCollection(
 *     // The pagination of the collection is executed when the collection is parsed
 *     new PaginatedCollection(
 *         // The QueryBuilder is executed when the collection is parsed
 *         new QueryBuilderCollection($queryBuilder)
 *     )
 * );
 * </code>
 *
 * @template TKey of array-key
 * @template T of object
 *
 * @implements ReadableCollectionInterface<TKey, T>
 * @implements SelectableInterface<TKey, T>
 */
final class QueryBuilderCollection implements ReadableCollectionInterface, SelectableInterface
{
    /**
     * @var ReadableCollectionInterface<TKey, T>
     */
    private ReadableCollectionInterface $collection;

    private bool $initialized = false;

    public function __construct(
        private readonly QueryBuilder $queryBuilder,
    ) {
    }

    /**
     * @param T $element
     */
    #[\Override]
    public function contains(mixed $element): bool
    {
        if (!$this->initialized) {
            $this->initialize();
        }

        return $this->collection->contains($element);
    }

    #[\Override]
    public function isEmpty(): bool
    {
        return 0 === $this->count();
    }

    /**
     * @param TKey $key
     */
    #[\Override]
    public function containsKey(mixed $key): bool
    {
        if ($this->initialized) {
            return $this->collection->containsKey($key);
        }

        return $this->getQueryBuilder()
            ->select('1')
            ->addCriteria(Criteria::create()->andWhere(new Comparison($this->getIdentifierFieldName(), Comparison::EQ, new Value($key))))
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @param TKey $key
     *
     * @return T|null
     */
    #[\Override]
    public function get(string|int $key): mixed
    {
        if ($this->initialized) {
            return $this->collection->get($key);
        }

        return $this->getQueryBuilder()
            ->addCriteria(Criteria::create()->andWhere(new Comparison($this->getIdentifierFieldName(), Comparison::EQ, new Value($key))))
            ->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult();
    }

    /**
     * @return list<TKey>
     */
    #[\Override]
    public function getKeys(): iterable
    {
        if ($this->initialized) {
            return $this->collection->getKeys();
        }

        return $this->getQueryBuilder()
            ->select($this->getIdentifierFieldName())
            ->getQuery()
            ->getScalarResult();
    }

    /**
     * @return list<T>
     */
    #[\Override]
    public function getValues(): iterable
    {
        if (!$this->initialized) {
            $this->initialize();
        }

        return $this->collection->getValues();
    }

    /**
     * @return array<T>
     */
    #[\Override]
    public function toArray(): array
    {
        if (!$this->initialized) {
            $this->initialize();
        }

        return $this->collection->toArray();
    }

    /**
     * @return T|false|null
     */
    #[\Override]
    public function first()
    {
        if ($this->initialized) {
            return $this->collection->first();
        }

        return $this->getQueryBuilder()
            ->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult();
    }

    /**
     * @return T|false
     */
    #[\Override]
    public function last()
    {
        if (!$this->initialized) {
            $this->initialize();
        }

        return $this->collection->last();
    }

    /**
     * @return TKey
     */
    #[\Override]
    public function key()
    {
        if (!$this->initialized) {
            $this->initialize();
        }

        return $this->collection->key();
    }

    /**
     * @return T
     */
    #[\Override]
    public function current()
    {
        if (!$this->initialized) {
            $this->initialize();
        }

        return $this->collection->current();
    }

    /**
     * @return T|false
     */
    #[\Override]
    public function next()
    {
        if (!$this->initialized) {
            $this->initialize();
        }

        return $this->collection->next();
    }

    /**
     * @return list<T>
     */
    #[\Override]
    public function slice(int $offset, ?int $length = null): iterable
    {
        if ($this->initialized) {
            return $this->collection->slice($offset, $length);
        }

        return $this->getQueryBuilder()
            ->getQuery()
            ->setFirstResult($offset)
            ->setMaxResults($length)
            ->getResult();
    }

    /**
     * @param \Closure(TKey, T): bool $p
     */
    #[\Override]
    public function exists(\Closure $p): bool
    {
        if (!$this->initialized) {
            $this->initialize();
        }

        return $this->collection->exists($p);
    }

    /**
     * @param \Closure(T, TKey): bool $p
     *
     * @return ReadableCollectionInterface<TKey, T>
     */
    #[\Override]
    public function filter(\Closure $p): ReadableCollectionInterface
    {
        if (!$this->initialized) {
            $this->initialize();
        }

        return $this->collection->filter($p);
    }

    /**
     * @param \Closure(T): T $func
     *
     * @return ReadableCollectionInterface<TKey, T>
     */
    #[\Override]
    public function map(\Closure $func): ReadableCollectionInterface
    {
        if (!$this->initialized) {
            $this->initialize();
        }

        return $this->collection->map($func);
    }

    /**
     * @param \Closure(TKey, T): bool $p
     *
     * @return array{0: ReadableCollectionInterface<TKey, T>, 1: ReadableCollectionInterface<TKey, T>}
     */
    #[\Override]
    public function partition(\Closure $p): array
    {
        if (!$this->initialized) {
            $this->initialize();
        }

        return $this->collection->partition($p);
    }

    /**
     * @param \Closure(TKey, T): bool $p
     */
    #[\Override]
    public function forAll(\Closure $p): bool
    {
        if (!$this->initialized) {
            $this->initialize();
        }

        return $this->collection->forAll($p);
    }

    /**
     * @param T $element
     *
     * @return TKey|false
     */
    #[\Override]
    public function indexOf(mixed $element)
    {
        if (!$this->initialized) {
            $this->initialize();
        }

        return $this->collection->indexOf($element);
    }

    /**
     * @param \Closure(TKey, T): bool $p
     *
     * @return T|null
     */
    #[\Override]
    public function findFirst(\Closure $p)
    {
        if (!$this->initialized) {
            $this->initialize();
        }

        return $this->collection->findFirst($p);
    }

    /**
     * @param \Closure(T, T): T $func
     *
     * @return T|null
     */
    #[\Override]
    public function reduce(\Closure $func, mixed $initial = null)
    {
        if (!$this->initialized) {
            $this->initialize();
        }

        return $this->collection->reduce($func, $initial);
    }

    #[\Override]
    public function count(): int
    {
        if ($this->initialized) {
            return $this->collection->count();
        }

        return $this->getQueryBuilder()
            ->select(\sprintf('COUNT(%s.%s)', $this->queryBuilder->getRootAliases()[0], $this->getIdentifierFieldName()))
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @return \Traversable<TKey, T>
     */
    #[\Override]
    public function getIterator(): \Traversable
    {
        if (!$this->initialized) {
            $this->initialize();
        }

        return $this->collection->getIterator();
    }

    /**
     * @return ReadableCollectionInterface<TKey, T>
     */
    #[\Override]
    public function matching(Criteria $criteria): ReadableCollectionInterface
    {
        if ($this->initialized) {
            return new ReadableCollection(new ArrayCollection($this->collection->toArray())->matching($criteria));
        }

        return new self($this->getQueryBuilder()->addCriteria($criteria));
    }

    /**
     * @param class-string $className
     *
     * @return ClassMetadata<T>
     */
    private function getClassMetadata(string $className): ClassMetadata
    {
        return $this->queryBuilder->getEntityManager()->getClassMetadata($className);
    }

    /**
     * Returns a clone of the {@see QueryBuilder} to don't alter the original one.
     */
    private function getQueryBuilder(): QueryBuilder
    {
        return clone $this->queryBuilder;
    }

    /**
     * Gets Entity identifier field name.
     */
    private function getIdentifierFieldName(): string
    {
        return $this->getClassMetadata($this->queryBuilder->getRootEntities()[0])->getIdentifierFieldNames()[0];
    }

    private function initialize(): void
    {
        if ($this->initialized) {
            return;
        }

        $this->collection = new ReadableCollection($this->queryBuilder->getQuery()->getResult());
        $this->initialized = true;
    }
}
