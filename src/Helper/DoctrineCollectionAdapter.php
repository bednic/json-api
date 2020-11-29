<?php

declare(strict_types=1);

namespace JSONAPI\Helper;

use Closure;
use Doctrine\Common\Collections\Collection as DoctrineCollection;
use JSONAPI\Data\Collection;
use Traversable;

/**
 * Class DoctrineCollectionAdapter
 *
 * Adapter for Doctrine\Common\Collections\Collection. In case you want work with ArrayCollection or
 * PersistentCollection and don't want to work with array from collection, just use this adapter to get advantages of
 * Collection interface, like lazy load or partial load.
 *
 * @package JSONAPI\Helper
 */
class DoctrineCollectionAdapter extends Collection
{
    private DoctrineCollection $collection;

    /**
     * @inheritDoc
     */
    public function __construct(DoctrineCollection $collection)
    {
        if (!interface_exists('Doctrine\Common\Collections\Collection')) {
            throw new \RuntimeException(
                'For using ' . __CLASS__ . ' you need install [doctrine/orm] <i>composer require doctrine/orm</i>.'
            );
        }
        parent::__construct();
        $this->collection = $collection;
    }

    /**
     * @inheritDoc
     */
    public function count(): int
    {
        return $this->collection->count();
    }

    /**
     * @inheritDoc
     */
    public function offsetExists($offset): bool
    {
        return $this->collection->offsetExists($offset);
    }

    /**
     * @inheritDoc
     */
    public function hasKey($key): bool
    {
        return $this->collection->containsKey($key);
    }

    /**
     * @inheritDoc
     */
    public function offsetGet($offset)
    {
        return $this->collection->offsetGet($offset);
    }

    /**
     * @inheritDoc
     */
    public function get($key)
    {
        return $this->collection->get($key);
    }

    /**
     * @inheritDoc
     */
    public function offsetSet($offset, $value): void
    {
        $this->collection->offsetSet($offset, $value);
    }

    /**
     * @inheritDoc
     */
    public function set($key, $value): void
    {
        $this->collection->set($key, $value);
    }

    /**
     * @inheritDoc
     */
    public function offsetUnset($offset): void
    {
        $this->collection->offsetUnset($offset);
    }

    /**
     * @inheritDoc
     */
    public function unset($key): void
    {
        $this->collection->offsetUnset($key);
    }

    /**
     * @inheritDoc
     */
    public function slice(int $offset, $length = null): Collection
    {
        return new DoctrineCollectionAdapter($this->collection->slice($offset, $length));
    }

    /**
     * @inheritDoc
     */
    public function filter(Closure $callback): Collection
    {
        return new DoctrineCollectionAdapter($this->collection->filter($callback));
    }

    /**
     * @inheritDoc
     */
    public function values(): array
    {
        return $this->collection->getValues();
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return $this->collection->toArray();
    }

    /**
     * @inheritDoc
     */
    public function has($item, $strict = false): bool
    {
        return $this->collection->contains($item);
    }

    /**
     * @inheritDoc
     */
    public function reset(): void
    {
        $this->collection->clear();
    }

    /**
     * @inheritDoc
     */
    public function push($item): int
    {
        $this->collection->add($item);
        return $this->collection->count();
    }

    /**
     * @inheritDoc
     */
    public function getIterator(): Traversable
    {
        return $this->collection->getIterator();
    }

    /**
     * @return DoctrineCollection
     */
    public function getCollection(): DoctrineCollection
    {
        return $this->collection;
    }
}
