<?php

declare(strict_types=1);

namespace JSONAPI\OAS;

use ReflectionClass;
use JSONAPI\Document\Serializable;

/**
 * Class Response
 *
 * @package JSONAPI\OAS
 */
class Response extends Reference implements Serializable
{
    /**
     * @var string
     */
    private string $description;
    /**
     * @var Header[]
     */
    private array $headers = [];
    /**
     * @var MediaType[]
     */
    private array $content = [];
    /**
     * @var Link[]
     */
    private array $links = [];

    /**
     * Response constructor.
     *
     * @param string $description
     */
    public function __construct(string $description)
    {
        $this->description = $description;
    }

    /**
     * @inheritDoc
     */
    public static function createReference(string $to, $origin): Response
    {
        /** @var Response $static */
        $static = (new ReflectionClass(__CLASS__))->newInstanceWithoutConstructor();
        $static->setRef($to, $origin);
        return $static;
    }

    /**
     * @param string $name
     * @param Header $header
     *
     * @return Response
     */
    public function addHeader(string $name, Header $header): Response
    {
        $this->headers[$name] = $header;
        return $this;
    }

    /**
     * @param string $key
     * @param Link   $link
     *
     * @return Response
     */
    public function addLink(string $key, Link $link): Response
    {
        $this->links[$key] = $link;
        return $this;
    }

    /**
     * @param string    $mediaType
     * @param MediaType $content
     *
     * @return Response
     */
    public function addContent(string $mediaType, MediaType $content): Response
    {
        $this->content[$mediaType] = $content;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        if ($this->isReference()) {
            return parent::jsonSerialize();
        }
        $ret = [
            'description' => $this->description
        ];
        if ($this->headers) {
            $ret['headers'] = $this->headers;
        }
        if ($this->content) {
            $ret['content'] = $this->content;
        }
        if ($this->links) {
            $ret['links'] = $this->links;
        }
        return (object)$ret;
    }
}
