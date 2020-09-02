<?php

declare(strict_types=1);

namespace JSONAPI\OAS;

use JSONAPI\OAS\Exception\ExclusivityCheckException;
use ReflectionClass;
use JSONAPI\Document\Serializable;

/**
 * Class Link
 *
 * @package JSONAPI\OAS
 */
class Link extends Reference implements Serializable
{

    /**
     * Exclusive of ::operationId
     *
     * @var string|null
     */
    private ?string $operationRef;
    /**
     * @var string|null
     */
    private ?string $operationId;
    /**
     * @var mixed[]
     */
    private array $parameters = [];
    /**
     * @var mixed
     */
    private $requestBody;
    /**
     * @var string|null
     */
    private ?string $description;
    /**
     * @var Server|null
     */
    private ?Server $server;

    /**
     * @inheritDoc
     */
    public static function createReference(string $to, $origin): Link
    {
        /** @var Link $static */
        $static = (new ReflectionClass(__CLASS__))->newInstanceWithoutConstructor();
        $static->setRef($to, $origin);
        return $static;
    }

    /**
     * @param string|null $operationRef
     *
     * @return Link
     * @throws ExclusivityCheckException
     */
    public function setOperationRef(?string $operationRef): Link
    {
        if ($this->operationId) {
            throw new ExclusivityCheckException();
        }
        $this->operationRef = $operationRef;
        return $this;
    }

    /**
     * @param string|null $operationId
     *
     * @return Link
     * @throws ExclusivityCheckException
     */
    public function setOperationId(?string $operationId): Link
    {
        if ($this->operationRef) {
            throw new ExclusivityCheckException();
        }
        $this->operationId = $operationId;
        return $this;
    }

    /**
     * @param string $name
     * @param mixed  $parameter
     *
     * @return Link
     */
    public function addParameter(string $name, $parameter): Link
    {
        $this->parameters[$name] = $parameter;
        return $this;
    }

    /**
     * @param mixed $requestBody
     *
     * @return Link
     */
    public function setRequestBody($requestBody)
    {
        $this->requestBody = $requestBody;
        return $this;
    }

    /**
     * @param string|null $description
     *
     * @return Link
     */
    public function setDescription(?string $description): Link
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @param Server|null $server
     *
     * @return Link
     */
    public function setServer(?Server $server): Link
    {
        $this->server = $server;
        return $this;
    }

    public function jsonSerialize()
    {
        if ($this->isReference()) {
            return parent::jsonSerialize();
        }
        $ret = [];
        if ($this->operationRef) {
            $ret['operationRef'] = $this->operationRef;
        }
        if ($this->operationId) {
            $ret['operationId'] = $this->operationId;
        }
        if ($this->parameters) {
            $ret['parameters'] = $this->parameters;
        }
        if ($this->requestBody) {
            $ret['requestBody'] = $this->requestBody;
        }
        if ($this->description) {
            $ret['description'] = $this->description;
        }
        if ($this->server) {
            $ret['server'] = $this->server;
        }
        return (object)$ret;
    }
}
