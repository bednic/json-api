<?php

declare(strict_types=1);

namespace JSONAPI\Document;

use Fig\Http\Message\StatusCodeInterface;
use JSONAPI\Exception\HasParameter;
use JSONAPI\Exception\HasPointer;
use JSONAPI\Exception\JsonApiException;
use JSONAPI\Helper\LinksTrait;
use JSONAPI\Helper\MetaTrait;
use Swaggest\JsonSchema\Exception\Error as SchemaError;
use Swaggest\JsonSchema\InvalidValue;
use Throwable;

/**
 * Class Error
 *
 * @package JSONAPI\Document
 */
final class Error implements Serializable, HasLinks, HasMeta
{
    use LinksTrait;
    use MetaTrait;

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
     * @var ErrorSource
     */
    private ErrorSource $source;

    /**
     * @param Throwable $exception
     *
     * @return Error
     */
    public static function fromException(Throwable $exception)
    {
        $self = new static();
        $self->setTitle(get_class($exception));
        $self->setStatus(StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR);
        $self->setCode((string)$exception->getCode());
        $self->setDetail($exception->getMessage());
        $source = ErrorSource::internal(
            $exception->getFile() . ':' . $exception->getLine(),
            $exception->getTraceAsString()
        );
        if ($exception instanceof JsonApiException) {
            $self->setStatus($exception->getStatus());
            if ($exception instanceof HasPointer) {
                $source = ErrorSource::pointer($exception->getPointer());
            } elseif ($exception instanceof HasParameter) {
                $source = ErrorSource::parameter($exception->getParameter());
            }
        } elseif ($exception instanceof InvalidValue) {
            list($message, $source) = $self::parseInvalidValue($exception->inspect());
            $self->setDetail($message);
        }
        $self->setSource($source);
        return $self;
    }

    /**
     * @param SchemaError $error
     *
     * @return array<string|ErrorSource>
     * @example [
     *      <string> message,
     *      <ErrorSource> source
     * ]
     */
    private static function parseInvalidValue(SchemaError $error): array
    {
        if ($error->subErrors) {
            return self::parseInvalidValue($error->subErrors[0]);
        } else {
            return [
                (string) preg_replace('/, data.+/', '', $error->error),
                ErrorSource::pointer($error->dataPointer)
            ];
        }
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
        $this->status = (string)$status;
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return (int)$this->status;
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
     * @param ErrorSource $source
     */
    public function setSource(ErrorSource $source): void
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
