<?php

declare(strict_types=1);

namespace JSONAPI\Document;

use stdClass;

/**
 * Class Document
 *
 * @package JSONAPI\Document
 */
final class Document implements Serializable, HasLinks, HasMeta
{
    use LinksExtension;
    use MetaExtension;

    public const MEDIA_TYPE = 'application/vnd.api+json';
    public const VERSION    = '1.0';

    /**
     * @var Error[]
     */
    private array $errors = [];

    /**
     * @var PrimaryData|null
     */
    private ?PrimaryData $data = null;

    /**
     * @var ResourceCollection<ResourceObject>
     */
    private ResourceCollection $included;

    /**
     * @var object
     */
    private object $jsonapi;

    /**
     * Document constructor.
     */
    public function __construct()
    {
        $this->jsonapi = new stdClass();
        $this->jsonapi->version = self::VERSION;
    }

    /**
     * @return PrimaryData|null
     */
    public function getData(): ?PrimaryData
    {
        return $this->data;
    }

    /**
     * @param PrimaryData|null $data
     */
    public function setData(?PrimaryData $data): void
    {
        if (count($this->errors) <= 0) {
            $this->data = $data;
        }
    }

    /**
     * @param ResourceCollection<ResourceObject> $includes
     */
    public function setIncludes(ResourceCollection $includes): void
    {
        $this->included = $includes;
    }

    /**
     * @param Error $error
     */
    public function addError(Error $error): void
    {
        $this->errors[] = $error;
    }

    /**
     * @param Meta $meta
     */
    public function setJSONAPIObjectMeta(Meta $meta): void
    {
        $this->jsonapi->meta = $meta;
    }

    /**
     * Specify data which should be serialized to JSON
     *
     * @link  https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return object data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize(): object
    {
        $ret = new stdClass();
        $ret->jsonapi = $this->jsonapi;
        if (count($this->errors) > 0) {
            $ret->errors = $this->errors;
        } else {
            $ret->data = $this->data;
            if (isset($this->included)) {
                $ret->included = $this->included;
            }
        }
        if ($this->hasLinks()) {
            $ret->links = $this->links;
        }
        if ($this->hasMeta()) {
            $ret->meta = $this->meta;
        }
        return $ret;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return (string)json_encode($this, JSON_PRESERVE_ZERO_FRACTION);
    }
}
