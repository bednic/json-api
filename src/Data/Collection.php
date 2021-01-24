<?php

declare(strict_types=1);

namespace JSONAPI\Data;

use ArrayAccess;
use ArrayIterator;
use Closure;
use Countable;
use ExpressionBuilder\Accessor\ObjectPropertyAccessor;
use ExpressionBuilder\Exception\ExpressionBuilderError;
use IteratorAggregate;
use JSONAPI\Exception\Data\CollectionException;
use JSONAPI\Exception\Data\NonObjectValue;
use Traversable;

/**
 * Class Collection
 *
 * @package JSONAPI\Data
 */
class Collection implements ArrayAccess, Countable, IteratorAggregate
{
    public const SORT_DESC = 'DESC';
    public const SORT_ASC = 'ASC';
    /**
     * @var array
     */
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
     * @param int|string $key
     *
     * @return bool
     */
    public function hasKey(int|string $key): bool
    {
        return $this->offsetExists($key);
    }

    /**
     * @inheritDoc
     */
    public function offsetExists(mixed $offset): bool
    {
        return array_key_exists($offset, $this->items);
    }

    /**
     * @param int|string $key
     *
     * @return mixed
     */
    public function get(int|string $key): mixed
    {
        return $this->offsetGet($key);
    }

    /**
     * @inheritDoc
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->items[$offset];
    }

    /**
     * @param int|string $key
     * @param mixed      $value
     */
    public function set(int|string $key, mixed $value): void
    {
        $this->offsetSet($key, $value);
    }

    /**
     * @inheritDoc
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->items[$offset] = $value;
    }

    /**
     * @param int|string $key
     */
    public function unset(int|string $key): void
    {
        $this->offsetUnset($key);
    }

    /**
     * @inheritDoc
     */
    public function offsetUnset(mixed $offset): void
    {
        unset($this->items[$offset]);
    }

    /**
     * @param int      $offset
     * @param int|null $length
     *
     * @return Collection
     */
    public function slice(int $offset, int $length = null): self
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
    public function has(mixed $item, bool $strict = false): bool
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
    public function push(mixed $item): int
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

    /**
     * Sort function for scalar collection
     *
     * @param string $orientation
     *
     * @return Collection
     */
    public function sort($orientation = self::SORT_ASC): self
    {
        assert(
            $this->items === array_filter($this->items, 'is_scalar'),
            '::sort can be used only on scalar data'
        );
        $orientation === self::SORT_DESC ? rsort($this->items) : sort($this->items);
        return $this;
    }

    /**
     * Sort function for object collection
     *
     * @param array $order
     *
     * @return Collection
     * @throws CollectionException
     */
    public function orderBy(array $order): self
    {
        assert(
            $this->items === array_filter($this->items, 'is_object'),
            '::orderBy works only on object collection'
        );
        $next = static function (): int {
            return 0;
        };
        foreach (array_reverse($order) as $field => $ordering) {
            $orientation = $ordering === self::SORT_DESC ? -1 : 1;
            $next        = static function ($a, $b) use ($field, $next, $orientation): int {
                $accessor = new ObjectPropertyAccessor();
                try {
                    $aValue = $accessor($a, $field);
                    $bValue = $accessor($b, $field);
                } catch (ExpressionBuilderError $exception) {
                    throw new CollectionException("Accessor error", 510, $exception);
                }
                if ($aValue === $bValue) {
                    return $next($a, $b);
                }
                return ($aValue > $bValue ? 1 : -1) * $orientation;
            };
        }
        uasort($this->items, $next);
        return $this;
    }
}
