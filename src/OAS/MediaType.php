<?php

declare(strict_types=1);

namespace JSONAPI\OAS;

use JSONAPI\OAS\Exception\ExclusivityCheckException;
use Tools\JSON\JsonSerializable;

/**
 * Class MediaType
 *
 * @package JSONAPI\OAS
 */
class MediaType implements JsonSerializable
{
    /**
     * @var Schema
     */
    private Schema $schema;
    /**
     * @var mixed
     */
    private $example;
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

    public function jsonSerialize()
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
