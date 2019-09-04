<?php
/**
 * Created by IntelliJ IDEA.
 * User: tomas
 * Date: 11.02.2019
 * Time: 12:46
 */

namespace JSONAPI\Document;

use Doctrine\Common\Collections\Collection;
use JSONAPI\Exception\Document\ForbiddenCharacter;
use JSONAPI\Exception\Document\ForbiddenDataType;
use JSONAPI\LinksTrait;
use JSONAPI\MetaTrait;
use JsonSerializable;

/**
 * Class Relationships
 *
 * @package JSONAPI\Document
 */
class Relationship extends Field implements JsonSerializable, HasLinks, HasMeta
{
    use LinksTrait;
    use MetaTrait;

    /**
     * @var ResourceObjectIdentifier|Collection<ResourceObjectIdentifier>
     */
    protected $data;

    /**
     * Relationship constructor.
     *
     * @param string                                                        $key
     * @param ResourceObjectIdentifier|Collection<ResourceObjectIdentifier> $data
     * @param array                                                         $links
     * @param Meta|null                                                     $meta
     *
     * @throws ForbiddenCharacter
     * @throws ForbiddenDataType
     */
    public function __construct(string $key, $data, array $links = [], Meta $meta = null)
    {
        parent::__construct($key, $data);
        $this->links = $links;
        $this->setMeta($meta ?? new Meta());
    }

    /**
     * @return array|mixed
     */
    public function jsonSerialize()
    {
        return [
            'data' => $this->getData(),
            'links' => $this->links
        ];
    }

    /**
     * @return ResourceObjectIdentifier|ResourceObjectIdentifier[]
     */
    public function getData()
    {
        if ($this->isCollection()) {
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
     * @return bool
     */
    public function isCollection(): bool
    {
        return $this->data instanceof Collection;
    }
}
