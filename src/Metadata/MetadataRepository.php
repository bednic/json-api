<?php

namespace JSONAPI\Metadata;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use JSONAPI\Exception\Metadata\MetadataNotFound;

class MetadataRepository
{
    private Collection $collection;
    private Collection $typeToClassMap;

    public function __construct()
    {
        $this->collection = new ArrayCollection();
        $this->typeToClassMap = new ArrayCollection();
    }

    /**
     * @param string $className
     *
     * @return ClassMetadata
     * @throws MetadataNotFound
     */
    public function getByClass(string $className): ClassMetadata
    {
        if ($this->collection->containsKey($className)) {
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
        if ($this->typeToClassMap->containsKey($type)) {
            return $this->getByClass($this->typeToClassMap->get($type));
        }
        throw new MetadataNotFound($type);
    }

    /**
     * @return ClassMetadata[]
     */
    public function getAll(): array
    {
        return $this->collection->toArray();
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
