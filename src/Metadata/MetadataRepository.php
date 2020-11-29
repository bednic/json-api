<?php

declare(strict_types=1);

namespace JSONAPI\Metadata;

use JSONAPI\Data\Collection;
use JSONAPI\Exception\Metadata\MetadataNotFound;

/**
 * Class MetadataRepository
 *
 * @package JSONAPI\Metadata
 */
class MetadataRepository
{
    private Collection $collection;
    private Collection $typeToClassMap;

    public function __construct()
    {
        $this->collection = new Collection();
        $this->typeToClassMap = new Collection();
    }

    /**
     * @param string $className
     *
     * @return ClassMetadata
     * @throws MetadataNotFound
     */
    public function getByClass(string $className): ClassMetadata
    {
        if ($this->collection->hasKey($className)) {
            return $this->collection[$className];
        }
        throw new MetadataNotFound($className);
    }

    /**
     * @param string $type - resource type
     *
     * @return ClassMetadata
     * @throws MetadataNotFound
     */
    public function getByType(string $type): ClassMetadata
    {
        if ($this->typeToClassMap->hasKey($type)) {
            return $this->getByClass($this->typeToClassMap->get($type));
        }
        throw new MetadataNotFound($type);
    }

    /**
     * @return ClassMetadata[]
     */
    public function getAll(): array
    {
        return $this->collection->values();
    }

    /**
     * @param ClassMetadata $metadata
     */
    public function add(ClassMetadata $metadata): void
    {
        $this->collection->set($metadata->getClassName(), $metadata);
        $this->typeToClassMap->set($metadata->getType(), $metadata->getClassName());
    }
}
