<?php

/**
 * Created by IntelliJ IDEA.
 * User: tomas
 * Date: 05.02.2019
 * Time: 13:14
 */

namespace JSONAPI\Document;

use Fig\Http\Message\StatusCodeInterface;
use JSONAPI\Exception\JsonApiException;
use JSONAPI\LinksTrait;
use JSONAPI\MetaTrait;
use JsonSerializable;

/**
 * Class Error
 *
 * @package JSONAPI\Document
 */
class Error implements JsonSerializable, HasLinks, HasMeta
{
    use LinksTrait;
    use MetaTrait;

    /**
     * @var string
     */
    private $id;
    /**
     * @var int
     */
    private $status = StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR;
    /**
     * @var string
     */
    private $code;
    /**
     * @var string
     */
    private $title;
    /**
     * @var string
     */
    private $detail;
    /**
     * @var array
     */
    private $source = [];

    /**
     * @param \Exception $exception
     *
     * @return Error
     */
    public static function fromException(\Exception $exception)
    {
        $self = new static();
        $self->setTitle(get_class($exception));
        $self->setCode($exception->getCode());
        $self->setDetail($exception->getMessage());
        return $self;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * @param string $code
     */
    public function setCode(string $code): void
    {
        $this->code = $code;
    }

    /**
     * @param int $status
     */
    public function setStatus(int $status): void
    {
        $this->status = $status;
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }


    /**
     * @param string $detail
     */
    public function setDetail(string $detail): void
    {
        $this->detail = $detail;
    }

    /**
     * @param string $id
     */
    public function setId(string $id): void
    {
        $this->id = $id;
    }

    /**
     * @param array $source
     */
    public function setSource(array $source): void
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
        return [
            'id' => $this->id,
            'links' => $this->links,
            'meta' => $this->meta,
            'status' => $this->status,
            'code' => $this->code,
            'title' => $this->title,
            'detail' => $this->detail,
            'source' => $this->source
        ];
    }
}
