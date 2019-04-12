<?php
/**
 * Created by IntelliJ IDEA.
 * User: tomas
 * Date: 04.02.2019
 * Time: 14:57
 */

namespace JSONAPI\Document;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class Resource
 * @package JSONAPI\Document
 */
class Resource extends ResourceIdentifier
{
    /**
     * @var Fields
     */
    private $fields;

    /**
     * @param ResourceIdentifier $resourceIdentifier
     * @param Fields             $fields
     */

    public function __construct(ResourceIdentifier $resourceIdentifier, Fields $fields)
    {
        parent::__construct($resourceIdentifier->type, $resourceIdentifier->id);
        $this->fields = $fields;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return int|string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return ArrayCollection
     */
    public function getMeta(): ArrayCollection
    {
        return $this->meta;
    }

    /**
     * Specify data which should be serialized to JSON
     * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        $ret = parent::jsonSerialize();
        if (!$this->fields->getAttributes()->isEmpty()) {
            $ret['attributes'] = $this->fields->getAttributes()->toArray();
        }
        if (!$this->fields->getRelationships()->isEmpty()) {
            $ret['relationships'] = $this->fields->getRelationships()->toArray();
        }
        return $ret;
    }
}
