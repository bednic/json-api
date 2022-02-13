<?php

declare(strict_types=1);

namespace JSONAPI\OAS;

use JSONAPI\Document\Serializable;
use JSONAPI\OAS\Type\Style;

/**
 * Class Encoding
 *
 * @package JSONAPI\OAS
 */
class Encoding implements Serializable
{
    /**
     * @var string|null
     */
    private ?string $contentType = null;
    /**
     * @var Header[]
     */
    private array $headers = [];
    /**
     * @var Style|null
     */
    private ?Style $style = null;
    /**
     * Default is false, for ::style='form' default is true
     *
     * @var bool
     */
    private ?bool $explode = null;
    /**
     * Default is false
     *
     * @var bool|null
     */
    private ?bool $allowReserved = null;

    /**
     * @param string|null $contentType
     *
     * @return Encoding
     */
    public function setContentType(?string $contentType): Encoding
    {
        $this->contentType = $contentType;
        return $this;
    }

    /**
     * @param Header $header
     *
     * @return Encoding
     */
    public function addHeader(Header $header): Encoding
    {
        $this->headers[$header->getName()] = $header;
        return $this;
    }

    /**
     * @param Style $style
     *
     * @return Encoding
     */
    public function setStyle(Style $style): Encoding
    {
        if ($style === Style::FORM) {
            $this->setExplode(true);
        }
        $this->style = $style;
        return $this;
    }

    /**
     * @param bool $explode
     *
     * @return Encoding
     */
    public function setExplode(bool $explode): Encoding
    {
        $this->explode = $explode;
        return $this;
    }

    /**
     * @param bool $allowReserved
     *
     * @return Encoding
     */
    public function setAllowReserved(bool $allowReserved): Encoding
    {
        $this->allowReserved = $allowReserved;
        return $this;
    }

    public function jsonSerialize(): object
    {
        $ret = [];
        if (!is_null($this->allowReserved)) {
            $ret['allowReserved'] = $this->allowReserved;
        }
        if (!is_null($this->explode)) {
            $ret['explode'] = $this->explode;
        }
        if ($this->contentType) {
            $ret['contentType'] = $this->contentType;
        }
        if ($this->headers) {
            $ret['headers'] = $this->headers;
        }
        if ($this->style) {
            $ret['style'] = $this->style->value;
        }
        return (object)$ret;
    }
}
