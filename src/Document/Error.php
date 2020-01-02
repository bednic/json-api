<?php

/**
 * Created by IntelliJ IDEA.
 * User: tomas
 * Date: 05.02.2019
 * Time: 13:14
 */

namespace JSONAPI\Document;

use Fig\Http\Message\StatusCodeInterface;
use JSONAPI\Exception\Http\UnsupportedParameter;
use JSONAPI\Exception\JsonApiException;
use JSONAPI\LinksTrait;
use JSONAPI\MetaTrait;
use JsonSerializable;
use Throwable;

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
    private string $id = "";
    /**
     * @var int
     */
    private int $status = StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR;
    /**
     * @var int
     */
    private int $code = 0;
    /**
     * @var string
     */
    private string $title = "";
    /**
     * @var string
     */
    private string $detail = "";
    /**
     * @todo this should be own class contains defined props
     * @var object
     */
    private $source;

    /**
     * @param Throwable $exception
     *
     * @return Error
     */
    public static function fromException(Throwable $exception)
    {
        $self = new static();
        $self->setTitle(get_class($exception));
        $self->setCode($exception->getCode());
        $self->setDetail($exception->getMessage());
        $source = [
            'location' => $exception->getFile() . ':' . $exception->getLine()
        ];
        if ($exception instanceof JsonApiException) {
            $self->setStatus($exception->getStatus());
            if ($exception instanceof UnsupportedParameter) {
                $source['parameter'] = $exception->getParameter();
            }
        }
        $self->setSource($source);
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
     * @param int $code
     */
    public function setCode(int $code): void
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
        $ret = [
            'id' => $this->id,
            'status' => (string)$this->status,
            'code' => (string)$this->code,
            'title' => $this->title,
            'detail' => $this->detail,
        ];
        if ($this->source) {
            $ret['source'] = $this->source;
        }
        if (!$this->getMeta()->isEmpty()) {
            $ret['meta'] = $this->meta;
        }
        if (count($this->getLinks()) > 0) {
            $ret['links'] = $this->links;
        }
        return $ret;
    }
}
