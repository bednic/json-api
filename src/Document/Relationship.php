<?php
/**
 * Created by IntelliJ IDEA.
 * User: tomas
 * Date: 11.02.2019
 * Time: 12:46
 */

namespace JSONAPI\Document;

use JSONAPI\Exception\Document\ForbiddenCharacter;
use JSONAPI\Exception\Document\ForbiddenDataType;
use JSONAPI\Utils\LinksImpl;
use JSONAPI\Utils\MetaImpl;
use JsonSerializable;

/**
 * Class Relationships
 *
 * @package JSONAPI\Document
 */
class Relationship extends Field implements JsonSerializable, HasLinks, HasMeta
{
    use LinksImpl;
    use MetaImpl;

    /**
     * Relationship constructor.
     *
     * @param string    $key
     * @param           $data
     * @param array     $links
     * @param Meta|null $meta
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
     * @return ResourceObjectIdentifier|ResourceObjectIdentifier[]
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return bool
     */
    public function isCollection()
    {
        return is_array($this->data);
    }

    /**
     * @return array|mixed
     */
    public function jsonSerialize()
    {
        return [
            'data' => $this->data,
            'links' => $this->links
        ];
    }
}
