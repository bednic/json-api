<?php

declare(strict_types=1);

namespace JSONAPI\Document;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use JSONAPI\Exception\Document\ResourceTypeMismatch;
use JsonSerializable;
use Traversable;

/**
 * Class ResourceCollection
 *
 * @package JSONAPI\Document\PrimaryData
 */
final class ResourceCollection implements PrimaryData, IteratorAggregate, Countable, JsonSerializable
{
    /**
     * @var ResourceObject[]|ResourceObjectIdentifier[]
     */
    private array $data = [];

    /**
     * Resource Type
     *
     * @var string|null
     */
    private static ?string $type = null;

    /**
     * ResourceCollection constructor.
     *
     * @param string|null $type - Type of accepted resources
     *
     */
    public function __construct(string $type = null)
    {
        self::$type = $type;
    }

    /**
     * @param ResourceObjectIdentifier|ResourceObject $resource
     *
     * @throws ResourceTypeMismatch
     */
    public function add(ResourceObjectIdentifier $resource)
    {
        if (!is_null(self::$type) && $resource->getType() !== self::$type) {
            throw new ResourceTypeMismatch($resource->getType(), self::$type);
        }
        $key = self::key($resource);
        if (!array_key_exists($key, $this->data)) {
            $this->data[$key] = $resource;
        }
    }

    /**
     * @param string $key
     *
     * @return ResourceObjectIdentifier|ResourceObject|null
     */
    public function get(string $key): ?ResourceObjectIdentifier
    {
        return $this->data[$key] ?? null;
    }

    /**
     * @param ResourceObjectIdentifier $resource
     *
     * @return bool
     */
    public function contains(ResourceObjectIdentifier $resource): bool
    {
        return in_array($resource, $this->data, true);
    }

    /**
     * @param ResourceObjectIdentifier $resource
     *
     * @return bool
     */
    public function remove(ResourceObjectIdentifier $resource): bool
    {
        $key = self::key($resource);
        if (array_key_exists($key, $this->data)) {
            unset($this->data[$key]);
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->data);
    }

    /**
     * Retrieve an external iterator
     *
     * @link  https://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     * @since 5.0.0
     */
    public function getIterator()
    {
        return new ArrayIterator($this->data);
    }

    private static function key(ResourceObjectIdentifier $resource): string
    {
        return $resource->getType() . $resource->getId();
    }

    /**
     * Specify data which should be serialized to JSON
     *
     * @link  https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return array_values($this->data);
    }
}
