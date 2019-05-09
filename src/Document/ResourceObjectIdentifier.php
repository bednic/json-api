<?php
/**
 * Created by IntelliJ IDEA.
 * User: tomas
 * Date: 11.02.2019
 * Time: 12:41
 */

namespace JSONAPI\Document;

use JSONAPI\Utils\MetaImpl;
use JsonSerializable;

/**
 * Class ResourceObjectIdentifier
 *
 * @package JSONAPI\Document
 */
class ResourceObjectIdentifier implements JsonSerializable, HasMeta
{

    use MetaImpl;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string|int|null
     */
    protected $id;

    protected $meta;

    /**
     * ResourceObjectIdentifier constructor.
     *
     * @param string          $type
     * @param string|int|null $id
     */
    public function __construct(string $type, $id)
    {
        $this->type = $type;
        $this->id = $id;
    }

    /**
     * @return int|string|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
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
        $ret = ['type' => $this->type, 'id' => $this->id];
        if ($this->meta) {
            $ret['meta'] = $this->meta;
        }
        return $ret;
    }
}
