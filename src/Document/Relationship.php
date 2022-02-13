<?php

declare(strict_types=1);

namespace JSONAPI\Document;

use JSONAPI\Data\Collection;
use JSONAPI\Exception\Document\ForbiddenDataType;

/**
 * Class Relationships
 *
 * @package JSONAPI\Document
 */
final class Relationship extends Field implements Serializable, HasLinks, HasMeta
{
    use LinksExtension;
    use MetaExtension;

    private bool $modified = false;

    /**
     * @param mixed $data
     *
     * @throws ForbiddenDataType
     */
    public function setData(mixed $data): void
    {
        if (
            $data instanceof ResourceObjectIdentifier ||
            $data instanceof ResourceCollection ||
            is_null($data)
        ) {
            $this->modified = true;
            parent::setData($data);
        } else {
            throw new ForbiddenDataType($this->getKey(), gettype($data));
        }
    }

    /**
     * @return object
     */
    public function jsonSerialize(): object
    {
        $ret = [];
        if ($this->hasData()) {
            $ret['data'] = $this->getData();
        }
        if ($this->hasLinks()) {
            $ret['links'] = $this->getLinks();
        }
        if ($this->hasMeta()) {
            $ret['meta'] = $this->getMeta();
        }
        return (object)$ret;
    }

    /**
     * @return bool
     */
    private function hasData(): bool
    {
        return $this->modified;
    }

    /**
     * @return ResourceObjectIdentifier|ResourceObjectIdentifier[]|null
     */
    public function getData(): ResourceObjectIdentifier | array | null
    {
        if ($this->data instanceof Collection) {
            return $this->data->values();
        }
        return $this->data;
    }
}
