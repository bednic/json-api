<?php

declare(strict_types=1);

namespace JSONAPI\Document;

use JSONAPI\Helper\LinksTrait;
use JSONAPI\Helper\MetaTrait;
use JsonSerializable;

/**
 * Class Document
 *
 * @package JSONAPI\Document
 */
final class Document implements JsonSerializable, HasLinks, HasMeta
{
    use LinksTrait;
    use MetaTrait;

    public const MEDIA_TYPE = 'application/vnd.api+json';
    public const VERSION = '1.0';

    /**
     * @var Error[]
     */
    private array $errors = [];

    /**
     * @var PrimaryData|null
     */
    private ?PrimaryData $data = null;

    /**
     * @var ResourceCollection
     */
    private ResourceCollection $included;

    /**
     * @var array
     */
    private array $jsonapi = [
        'version' => self::VERSION
    ];

    /**
     * Document constructor.
     */
    public function __construct()
    {
        $this->included = new ResourceCollection();
    }

    /**
     * @param PrimaryData|null $data
     */
    public function setData(?PrimaryData $data): void
    {
        if (count($this->errors) > 0) {
            return;
        }
        $this->data = $data;
    }

    /**
     * @return PrimaryData|null
     */
    public function getData(): ?PrimaryData
    {
        return $this->data;
    }

    /**
     * @param ResourceCollection $includes
     */
    public function setIncludes(ResourceCollection $includes)
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
        $this->jsonapi['meta'] = $meta;
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
        $ret = ['jsonapi' => $this->jsonapi];

        if (count($this->errors) > 0) {
            $ret['errors'] = $this->errors;
        } else {
            $ret['data'] = $this->data;
            if ($this->included->count() > 0) {
                $ret['included'] = $this->included;
            }
        }
        if ($this->hasLinks()) {
            $ret['links'] = $this->links;
        }
        if (!$this->getMeta()->isEmpty()) {
            $ret['meta'] = $this->meta;
        }
        return $ret;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return (string)json_encode($this);
    }
}
