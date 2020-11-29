<?php

declare(strict_types=1);

namespace JSONAPI\Data;

use ArrayAccess;
use ArrayIterator;
use Closure;
use Countable;
use IteratorAggregate;
use Traversable;

/**
 * Class Collection
 *
 * @package JSONAPI\Data
 */
class Collection implements ArrayAccess, Countable, IteratorAggregate
{

    private array $items;

    /**
     * Collection constructor.
     *
     * @param array $items
     */
    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    /**
     * @inheritDoc
     */
    public function count(): int
    {
        return count($this->items);
    }

    /**
     * @inheritDoc
     */
    public function offsetExists($offset): bool
    {
        return array_key_exists($offset, $this->items);
    }

    /**
     * @param int|string $key
     *
     * @return bool
     */
    public function hasKey($key): bool
    {
        return $this->offsetExists($key);
    }

    /**
     * @inheritDoc
     */
    public function offsetGet($offset)
    {
        return $this->items[$offset];
    }

    /**
     * @param int|string $key
     *
     * @return mixed
     */
    public function get($key)
    {
        return $this->offsetGet($key);
    }

    /**
     * @inheritDoc
     */
    public function offsetSet($offset, $value): void
    {
        $this->items[$offset] = $value;
    }

    /**
     * @param int|string $key
     * @param mixed      $value
     */
    public function set($key, $value): void
    {
        $this->offsetSet($key, $value);
    }

    /**
     * @inheritDoc
     */
    public function offsetUnset($offset): void
    {
        unset($this->items[$offset]);
    }

    /**
     * @param int|string $key
     */
    public function unset($key): void
    {
        $this->offsetUnset($key);
    }

    /**
     * @param int  $offset
     * @param null $length
     *
     * @return Collection
     */
    public function slice(int $offset, $length = null): self
    {
        return new self(array_slice($this->items, $offset, $length));
    }

    /**
     * @param Closure $callback
     *
     * @return Collection
     */
    public function filter(Closure $callback): self
    {
        return new self(array_filter($this->items, $callback, ARRAY_FILTER_USE_BOTH));
    }

    /**
     * Returns Collection as array
     *
     * @return array
     */
    public function values(): array
    {
        return array_values($this->items);
    }

    /**
     * Returns internal items as is
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->items;
    }

    /**
     * @param mixed $item
     * @param bool  $strict
     *
     * @return bool
     */
    public function has($item, $strict = false): bool
    {
        return in_array($item, $this->items, $strict);
    }

    /**
     * Resets Collection
     */
    public function reset(): void
    {
        $this->items = [];
    }

    /**
     * @param mixed $item
     *
     * @return int
     */
    public function push($item): int
    {
        return array_push($this->items, $item);
    }

    /**
     * @inheritDoc
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }
}
