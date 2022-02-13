<?php

declare(strict_types=1);

namespace JSONAPI\OAS;

use JSONAPI\Document\Serializable;
use JSONAPI\Exception\OAS\ExclusivityCheckException;

/**
 * Class MediaType
 *
 * @package JSONAPI\OAS
 */
class MediaType implements Serializable
{
    /**
     * @var Schema|null
     */
    private ?Schema $schema = null;
    /**
     * @var mixed
     */
    private mixed $example = null;
    /**
     * @var Example[]
     */
    private array $examples = [];
    /**
     * @var Encoding[]
     */
    private array $encoding = [];

    /**
     * @param Schema $schema
     *
     * @return MediaType
     */
    public function setSchema(Schema $schema): MediaType
    {
        $this->schema = $schema;
        return $this;
    }

    /**
     * @param mixed $example
     *
     * @return MediaType
     * @throws ExclusivityCheckException
     */
    public function setExample($example): MediaType
    {
        if ($this->examples) {
            throw new ExclusivityCheckException();
        }
        $this->example = $example;
        return $this;
    }

    /**
     * @param string  $key
     * @param Example $example
     *
     * @return MediaType
     * @throws ExclusivityCheckException
     */
    public function addExample(string $key, Example $example): MediaType
    {
        if ($this->example) {
            throw new ExclusivityCheckException();
        }
        $this->examples[$key] = $example;
        return $this;
    }

    /**
     * @param string   $name
     * @param Encoding $encoding
     *
     * @return MediaType
     */
    public function addEncoding(string $name, Encoding $encoding): MediaType
    {
        $this->encoding[$name] = $encoding;
        return $this;
    }

    public function jsonSerialize(): object
    {
        $ret = [];
        if ($this->schema) {
            $ret['schema'] = $this->schema;
        }
        if ($this->example) {
            $ret['example'] = $this->example;
        }
        if ($this->examples) {
            $ret['examples'] = $this->examples;
        }
        if ($this->encoding) {
            $ret['encoding'] = $this->encoding;
        }
        return (object)$ret;
    }
}
