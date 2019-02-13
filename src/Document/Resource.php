<?php
/**
 * Created by IntelliJ IDEA.
 * User: tomas
 * Date: 04.02.2019
 * Time: 14:57
 */

namespace OpenAPI\Document;

class Resource extends ResourceIdentifier implements \JsonSerializable
{
    /**
     * @var Fields
     */
    private $fields;
    /**
     * @var array
     */
    private $links = [];

    /**
     *
     * @param ResourceIdentifier $resourceIdentifier
     * @param Fields $fields
     */

    public function __construct(ResourceIdentifier $resourceIdentifier, Fields $fields)
    {
        parent::__construct($resourceIdentifier->type, $resourceIdentifier->id);
        $this->fields = $fields;
        $this->links = Links::createResourceLinks($resourceIdentifier);
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
     * @return array
     */
    public function getLinks(): array
    {
        return $this->links;
    }

    /**
     * @return array
     */
    public function getMeta(): array
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
        $ret['attributes'] = $this->fields->getAttributes()->toArray();
        $ret['relationships'] = $this->fields->getRelationships()->toArray();
        if($this->links){
            $ret['links'] = $this->links;
        }
        return $ret;
    }
}
