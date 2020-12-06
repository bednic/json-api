<?php

declare(strict_types=1);

namespace JSONAPI\OAS;

use JSONAPI\Document\Serializable;
use ReflectionClass;

/**
 * Class RequestBody
 *
 * @package JSONAPI\OAS
 */
class RequestBody extends Reference implements Serializable
{
    /**
     * @var string|null
     */
    private ?string $description = null;
    /**
     * @var MediaType[]
     */
    private array $content = [];
    /**
     * Default is false
     *
     * @var bool|null
     */
    private ?bool $required = null;

    /**
     * RequestBody constructor.
     *
     * @param string    $mediaType
     * @param MediaType $content
     */
    public function __construct(string $mediaType, MediaType $content)
    {
        $this->addContent($mediaType, $content);
    }

    /**
     * @param string    $mediaType
     * @param MediaType $content
     *
     * @return $this
     */
    public function addContent(string $mediaType, MediaType $content): RequestBody
    {
        $this->content[$mediaType] = $content;
        return $this;
    }

    /**
     * @param string                                                                            $to
     * @param SecurityScheme|Schema|Response|RequestBody|Parameter|Header|Link|Example|Callback $origin
     *
     * @return RequestBody
     */
    public static function createReference(string $to, $origin): RequestBody
    {
        /** @var RequestBody $static */
        $static = (new ReflectionClass(__CLASS__))->newInstanceWithoutConstructor();
        $static->setRef($to, $origin);
        return $static;
    }

    /**
     * @param string|null $description
     *
     * @return RequestBody
     */
    public function setDescription(?string $description): RequestBody
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @param bool $required
     *
     * @return RequestBody
     */
    public function setRequired(bool $required): RequestBody
    {
        $this->required = $required;
        return $this;
    }

    public function jsonSerialize()
    {
        if ($this->isReference()) {
            return parent::jsonSerialize();
        }
        $ret = [];
        if (!is_null($this->required)) {
            $ret['required'] = $this->required;
        }
        if ($this->description) {
            $ret['description'] = $this->description;
        }
        if ($this->content) {
            $ret['content'] = $this->content;
        }
        return (object)$ret;
    }
}
