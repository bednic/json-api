<?php
/**
 * Created by IntelliJ IDEA.
 * User: tomas
 * Date: 11.02.2019
 * Time: 12:41
 */

namespace JSONAPI\Document;


use Doctrine\Common\Collections\ArrayCollection;

class ResourceIdentifier implements \JsonSerializable
{

    /**
     * @var string
     */
    protected $type;
    /**
     * @var mixed
     */
    protected $id;
    /**
     * @var ArrayCollection
     */
    protected $meta;

    /**
     * ResourceIdentifier constructor.
     * @param $type
     * @param $id
     */
    public function __construct(string $type, $id)
    {
        $this->type = $type;
        $this->id = $id;
        $this->meta = new ArrayCollection();
    }

    public function addMeta($key, $value)
    {
        $this->meta[$key] = $value;
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
        if (!$this->meta->isEmpty()) {
            $ret['meta'] = $this->meta->toArray();
        }
        return $ret;
    }
}
