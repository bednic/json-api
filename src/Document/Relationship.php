<?php

declare(strict_types=1);

namespace JSONAPI\Document;

use Doctrine\Common\Collections\Collection;
use JSONAPI\Exception\Document\ForbiddenDataType;
use JSONAPI\Helper\LinksTrait;
use JSONAPI\Helper\MetaTrait;
use JsonSerializable;

/**
 * Class Relationships
 *
 * @package JSONAPI\Document
 */
final class Relationship extends Field implements JsonSerializable, HasLinks, HasMeta
{
    use LinksTrait;
    use MetaTrait;

    /**
     * @return ResourceObjectIdentifier|ResourceObjectIdentifier[]
     */
    public function getData()
    {
        if ($this->data instanceof Collection) {
            return $this->data->toArray();
        }
        return $this->data;
    }

    /**
     * @param ResourceObjectIdentifier|Collection<ResourceObjectIdentifier>|null $data
     *
     * @throws ForbiddenDataType
     */
    public function setData($data): void
    {
        if ($data instanceof ResourceObjectIdentifier || $data instanceof Collection || is_null($data)) {
            parent::setData($data);
        } else {
            throw new ForbiddenDataType(gettype($data));
        }
    }

    /**
     * @return array|mixed
     */
    public function jsonSerialize()
    {
        $ret = [
            'data' => $this->getData()
        ];
        if ($this->hasLinks()) {
            $ret['links'] = $this->getLinks();
        }
        if (!$this->getMeta()->isEmpty()) {
            $ret['meta'] = $this->getMeta();
        }
        return $ret;
    }
}
