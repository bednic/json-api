<?php
/**
 * Created by IntelliJ IDEA.
 * User: tomas
 * Date: 11.02.2019
 * Time: 12:41
 */

namespace OpenAPI\Document;


class ResourceIdentifier implements \JsonSerializable
{

    protected $type;
    protected $id;
    protected $meta = null;

    /**
     * ResourceIdentifier constructor.
     * @param $type
     * @param $id
     * @param null $meta
     */
    public function __construct(string $type, $id)
    {
        $this->type = $type;
        $this->id = $id;
    }

    public function setMeta($meta)
    {
        $this->meta = $meta;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
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
        $ret = ['type' => $this->type, 'id' => $this->id];
        if ($this->meta) {
            $ret['meta'] = $this->meta;
        }
        return $ret;
    }
}
