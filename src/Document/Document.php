<?php

/**
 * Created by IntelliJ IDEA.
 * User: tomas
 * Date: 04.02.2019
 * Time: 14:48
 */

namespace JSONAPI\Document;

use JSONAPI\Exception\Document\DocumentException;
use JSONAPI\Exception\Http\BadRequest;
use JSONAPI\LinksTrait;
use JSONAPI\MetaTrait;
use JSONAPI\Uri\Fieldset\FieldsetInterface;
use JSONAPI\Uri\Fieldset\FieldsetParser;
use JSONAPI\Uri\Fieldset\SortParser;
use JSONAPI\Uri\Filtering\CriteriaFilterParser;
use JSONAPI\Uri\Filtering\FilterInterface;
use JSONAPI\Uri\Filtering\FilterParserInterface;
use JSONAPI\Uri\Inclusion\InclusionInterface;
use JSONAPI\Uri\Inclusion\InclusionParser;
use JSONAPI\Uri\LinkFactory;
use JSONAPI\Uri\Pagination\LimitOffsetPagination;
use JSONAPI\Uri\Pagination\PaginationInterface;
use JSONAPI\Uri\Pagination\PaginationParserInterface;
use JSONAPI\Uri\Path\PathInterface;
use JSONAPI\Uri\Path\PathParser;
use JSONAPI\Uri\Sorting\SortInterface;
use JSONAPI\Uri\UriPartInterface;
use JsonSerializable;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Class Document
 *
 * @package JSONAPI\Document
 */
class Document implements JsonSerializable, HasLinks, HasMeta
{
    use LinksTrait;
    use MetaTrait;

    public const MEDIA_TYPE = "application/vnd.api+json";
    public const VERSION = "1.0";

    /**
     * @var Error[]
     */
    private array $errors = [];

    /**
     * @var PrimaryData
     */
    private PrimaryData $data;

    /**
     * @var ResourceCollection
     */
    private ResourceCollection $included;

    /**
     * Document constructor.
     */
    public function __construct()
    {
        $this->included = new ResourceCollection();
    }

    /**
     * @param PrimaryData $data
     */
    public function setData(PrimaryData $data): void
    {
        if (count($this->errors) > 0) {
            return;
        }
        $this->data = $data;
    }

    /**
     * @return PrimaryData
     */
    public function getData(): PrimaryData
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
     * Specify data which should be serialized to JSON
     *
     * @link  https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        $ret["jsonapi"] = ["version" => self::VERSION];
        if (count($this->errors) > 0) {
            $ret["errors"] = $this->errors;
        } else {
            $ret['data'] = $this->data;
        }
        if (count($this->included) > 0) {
            $ret["included"] = $this->included;
        }
        if ($this->hasLinks()) {
            $ret["links"] = $this->links;
        }
        if (!$this->getMeta()->isEmpty()) {
            $ret["meta"] = $this->meta;
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
