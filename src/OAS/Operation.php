<?php

declare(strict_types=1);

namespace JSONAPI\OAS;

use JSONAPI\Document\Serializable;
use JSONAPI\Exception\OAS\DuplicationEntryException;

/**
 * Class Operation
 *
 * @package JSONAPI\OAS
 */
class Operation implements Serializable
{

    /**
     * @var Tag[]
     */
    private array $tags = [];
    /**
     * @var string|null
     */
    private ?string $summary = null;
    /**
     * @var string|null
     */
    private ?string $description = null;
    /**
     * @var ExternalDocumentation|null
     */
    private ?ExternalDocumentation $externalDocs = null;
    /**
     * @var string|null
     */
    private ?string $operationId = null;
    /**
     * @var Parameter[]
     */
    private array $parameters = [];
    /**
     * @var RequestBody
     */
    private ?RequestBody $requestBody = null;
    /**
     * @var Responses
     */
    private Responses $responses;

    /**
     * @var Callback[]
     */
    private array $callbacks = [];
    /**
     * @var bool|null
     */
    private ?bool $deprecated = null;
    /**
     * @var SecurityRequirement[]
     */
    private array $security = [];
    /**
     * @var Server[]
     */
    private array $servers = [];

    /**
     * Operation constructor.
     */
    public function __construct()
    {
        $this->responses = new Responses();
    }

    /**
     * @return Operation
     */
    public static function new(): Operation
    {
        return new self();
    }

    /**
     * @param Tag $tag
     *
     * @return Operation
     */
    public function addTag(Tag $tag): Operation
    {
        $this->tags[] = $tag;
        return $this;
    }

    /**
     * @param string|null $summary
     *
     * @return Operation
     */
    public function setSummary(?string $summary): Operation
    {
        $this->summary = $summary;
        return $this;
    }

    /**
     * @param string|null $description
     *
     * @return Operation
     */
    public function setDescription(?string $description): Operation
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @param ExternalDocumentation|null $externalDocs
     *
     * @return Operation
     */
    public function setExternalDocs(?ExternalDocumentation $externalDocs): Operation
    {
        $this->externalDocs = $externalDocs;
        return $this;
    }

    /**
     * @param string|null $operationId
     *
     * @return Operation
     */
    public function setOperationId(?string $operationId): Operation
    {
        $this->operationId = $operationId;
        return $this;
    }

    /**
     * @param Parameter $parameter
     *
     * @return Operation
     * @throws DuplicationEntryException
     */
    public function addParameter(Parameter $parameter): Operation
    {
        if (!$parameter->isReference()) {
            foreach ($this->parameters as $p) {
                if ($parameter->getUID() === $p->getUID()) {
                    throw new DuplicationEntryException();
                }
            }
        }
        $this->parameters[] = $parameter;
        return $this;
    }

    /**
     * @param RequestBody $requestBody
     *
     * @return Operation
     */
    public function setRequestBody(RequestBody $requestBody): Operation
    {
        $this->requestBody = $requestBody;
        return $this;
    }

    /**
     * @return Responses
     */
    public function getResponses(): Responses
    {
        return $this->responses;
    }

    /**
     * @param Responses $responses
     *
     * @return Operation
     */
    public function setResponses(Responses $responses): Operation
    {
        $this->responses = $responses;
        return $this;
    }

    /**
     * @param Callback $callback
     *
     * @return Operation
     */
    public function addCallback(Callback $callback): Operation
    {
        $this->callbacks[] = $callback;
        return $this;
    }

    /**
     * @param bool $deprecated
     *
     * @return Operation
     */
    public function setDeprecated(bool $deprecated): Operation
    {
        $this->deprecated = $deprecated;
        return $this;
    }

    /**
     * @param SecurityRequirement $security
     *
     * @return Operation
     */
    public function addSecurity(SecurityRequirement $security): Operation
    {
        $this->security[] = $security;
        return $this;
    }

    /**
     * @param Server $server
     *
     * @return Operation
     */
    public function addServer(Server $server): Operation
    {
        $this->servers[] = $server;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        $ret = [
            'responses' => $this->responses,
        ];
        if (!is_null($this->deprecated)) {
            $ret['deprecated'] = $this->deprecated;
        }
        if ($this->tags) {
            $ret['tags'] = $this->tags;
        }
        if ($this->summary) {
            $ret['summary'] = $this->summary;
        }
        if ($this->description) {
            $ret['description'] = $this->description;
        }
        if ($this->externalDocs) {
            $ret['externalDocs'] = $this->externalDocs;
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
        if ($this->callbacks) {
            $ret['callbacks'] = $this->callbacks;
        }
        if ($this->security) {
            $ret['security'] = $this->security;
        }
        if ($this->servers) {
            $ret['servers'] = $this->servers;
        }
        return (object)$ret;
    }
}
