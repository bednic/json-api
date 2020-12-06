<?php

declare(strict_types=1);

namespace JSONAPI\Document;

use JSONAPI\Document\Error\Source;

/**
 * Class Error
 *
 * @package JSONAPI\Document
 */
final class Error implements Serializable, HasLinks, HasMeta
{
    use LinksExtension;
    use MetaExtension;

    /**
     * @var string
     */
    private string $id;
    /**
     * @var string
     */
    private string $status;
    /**
     * @var string
     */
    private string $code;
    /**
     * @var string
     */
    private string $title;
    /**
     * @var string
     */
    private string $detail;
    /**
     * @var Source
     */
    private Source $source;

    /**
     *  a short, human-readable summary of the problem that SHOULD NOT change from occurrence to occurrence of the
     *  problem, except for purposes of localization.
     *
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * an application-specific error code, expressed as a string value.
     *
     * @param string $code
     */
    public function setCode(string $code): void
    {
        $this->code = $code;
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return (int)$this->status;
    }

    /**
     * the HTTP status code applicable to this problem, expressed as a string value.
     *
     * @param int $status
     */
    public function setStatus(int $status): void
    {
        $this->status = (string)$status;
    }

    /**
     * a human-readable explanation specific to this occurrence of the problem. Like title, this field’s value can be
     * localized.
     *
     * @param string $detail
     */
    public function setDetail(string $detail): void
    {
        $this->detail = $detail;
    }

    /**
     * a unique identifier for this particular occurrence of the problem.
     *
     * @param string $id
     */
    public function setId(string $id): void
    {
        $this->id = $id;
    }

    /**
     * an object containing references to the source of the error.
     *
     * @param Source $source
     */
    public function setSource(Source $source): void
    {
        $this->source = $source;
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
        $ret = [];
        if (isset($this->id)) {
            $ret['id'] = $this->id;
        }
        if (isset($this->status)) {
            $ret['status'] = $this->status;
        }
        if (isset($this->code)) {
            $ret['code'] = $this->code;
        }
        if (isset($this->title)) {
            $ret['title'] = $this->title;
        }
        if (isset($this->detail)) {
            $ret['detail'] = $this->detail;
        }
        if (isset($this->source)) {
            $ret['source'] = $this->source;
        }
        if (!$this->getMeta()->isEmpty()) {
            $ret['meta'] = $this->meta;
        }
        if (count($this->getLinks()) > 0) {
            $ret['links'] = $this->links;
        }
        return (object)$ret;
    }
}
