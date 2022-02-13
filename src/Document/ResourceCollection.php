<?php

declare(strict_types=1);

namespace JSONAPI\Document;

use JSONAPI\Data\Collection;

/**
 * Class ResourceCollection
 *
 * @package JSONAPI\Document\PrimaryData
 */
final class ResourceCollection extends Collection implements PrimaryData, Serializable
{
    /**
     * ResourceCollection constructor.
     *
     * @param ResourceObject[]|ResourceObjectIdentifier[] $items
     */
    public function __construct(array $items = [])
    {
        parent::__construct([]);
        foreach ($items as $item) {
            $this->add($item);
        }
    }

    /**
     * @param ResourceObjectIdentifier | ResourceObject $resource
     */
    public function add(ResourceObjectIdentifier | ResourceObject $resource): void
    {
        $key = $this->key($resource);
        if (!$this->hasKey($key)) {
            $this->set($key, $resource);
        }
    }

    /**
     * @param ResourceObjectIdentifier $resource
     *
     * @return string
     */
    private function key(ResourceObjectIdentifier $resource): string
    {
        return $resource->getType() . $resource->getId();
    }

    /**
     * @param ResourceObjectIdentifier | ResourceObject $item
     *
     * @return int
     */
    public function push(mixed $item): int
    {
        $this->add($item);
        return $this->count();
    }

    /**
     * @param string $type Type of resource
     * @param string $id   ID of resource
     *
     * @return ResourceObjectIdentifier | ResourceObject | null
     */
    public function find(string $type, string $id): ResourceObjectIdentifier | ResourceObject | null
    {
        $key = $type . $id;
        return $this->hasKey($key) ? $this->get($key) : null;
    }

    /**
     * @param ResourceObjectIdentifier $resource
     *
     * @return bool
     */
    public function remove(ResourceObjectIdentifier $resource): bool
    {
        $key = $this->key($resource);
        if ($this->hasKey($key)) {
            $this->unset($key);
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param ResourceObjectIdentifier $resource
     *
     * @return bool
     */
    public function contains(ResourceObjectIdentifier $resource): bool
    {
        return $this->has($resource, true);
    }

    /**
     * Specify data which should be serialized to JSON
     *
     * @link  https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return array<ResourceObject|ResourceObjectIdentifier> data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize(): array
    {
        return $this->values();
    }
}
