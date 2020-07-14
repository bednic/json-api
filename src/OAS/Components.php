<?php

declare(strict_types=1);

namespace JSONAPI\OAS;

use JSONAPI\OAS\Exception\InvalidArgumentException;
use JSONAPI\OAS\Exception\ReferencedObjectNotExistsException;

/**
 * Class Components
 *
 * @package JSONAPI\OAS
 */
class Components implements \JsonSerializable
{
    /**
     * @var array<string, Schema|Reference>
     */
    private array $schemas = [];
    /**
     * @var array<string, Response|Reference>
     */
    private array $responses = [];
    /**
     * @var array<string, Parameter|Reference>
     */
    private array $parameters = [];
    /**
     * @var array<string, Example|Reference>
     */
    private array $examples = [];
    /**
     * @var array<string, RequestBody|Reference>
     */
    private array $requestBodies = [];
    /**
     * @var array<string, Header>
     */
    private array $headers = [];
    /**
     * @var array<string, SecurityScheme|Reference>
     */
    private array $securitySchemes = [];
    /**
     * @var array<string, Link|Reference>
     */
    private array $links = [];
    /**
     * @var array<string, Callback|Reference>
     */
    private array $callbacks = [];

    /**
     * @param string $key
     * @param Schema $schema
     *
     * @return $this
     */
    public function addSchema(string $key, Schema $schema): Components
    {
        if ($this->schemas === null) {
            $this->schemas = [];
        }
        $this->schemas[$key] = $schema;
        return $this;
    }

    /**
     * @param string $key
     *
     * @return Schema
     * @throws ReferencedObjectNotExistsException
     */
    public function createSchemaReference(string $key): Schema
    {
        if (!array_key_exists($key, $this->schemas)) {
            throw new ReferencedObjectNotExistsException();
        }
        $to = '#/components/schemas/' . $key;
        return Schema::createReference($to);
    }

    /**
     * @param string   $key
     * @param Response $response
     *
     * @return $this
     */
    public function addResponse(string $key, Response $response): Components
    {
        $this->responses[$key] = $response;
        return $this;
    }

    /**
     * @param string $key
     *
     * @return Response
     * @throws ReferencedObjectNotExistsException
     */
    public function createResponseReference(string $key): Response
    {
        if (!array_key_exists($key, $this->responses)) {
            throw new ReferencedObjectNotExistsException();
        }
        $to = '#/components/responses/' . $key;
        return Response::createReference($to);
    }

    /**
     * @param string    $key
     * @param Parameter $parameter
     *
     * @return $this
     * @throws InvalidArgumentException
     */
    public function addParameter(string $key, Parameter $parameter): Components
    {
        if (empty($key)) {
            throw new InvalidArgumentException();
        }
        $this->parameters[$key] = $parameter;
        return $this;
    }

    /**
     * @param string $key
     *
     * @return Parameter
     * @throws ReferencedObjectNotExistsException
     */
    public function createParameterReference(string $key): Parameter
    {
        if (!array_key_exists($key, $this->parameters)) {
            throw new ReferencedObjectNotExistsException();
        }
        $to = '#/components/parameters/' . $key;
        return Parameter::createReference($to);
    }

    /**
     * @param string  $key
     * @param Example $example
     *
     * @return Components
     */
    public function addExample(string $key, Example $example): Components
    {
        if ($this->examples === null) {
            $this->examples = [];
        }
        $this->examples[$key] = $example;
        return $this;
    }

    /**
     * @param string $key
     *
     * @return Example
     * @throws ReferencedObjectNotExistsException
     */
    public function createExampleReference(string $key): Example
    {
        if (!array_key_exists($key, $this->examples)) {
            throw new ReferencedObjectNotExistsException();
        }
        $to = '#/components/examples/' . $key;
        return Example::createReference($to);
    }

    /**
     * @param string      $key
     * @param RequestBody $requestBody
     *
     * @return $this
     */
    public function addRequestBody(string $key, RequestBody $requestBody): Components
    {
        if ($this->requestBodies === null) {
            $this->requestBodies = [];
        }
        $this->requestBodies[$key] = $requestBody;
        return $this;
    }

    /**
     * @param string $key
     *
     * @return RequestBody
     * @throws ReferencedObjectNotExistsException
     */
    public function createRequestBodyReference(string $key): RequestBody
    {
        if (!array_key_exists($key, $this->requestBodies)) {
            throw new ReferencedObjectNotExistsException();
        }
        $to = '#/components/requestBodies/' . $key;
        return RequestBody::createReference($to);
    }

    /**
     * @param Header $header
     *
     * @return $this
     */
    public function addHeader(Header $header): Components
    {
        if ($this->headers === null) {
            $this->headers = [];
        }
        $this->headers[$header->getName()] = $header;
        return $this;
    }

    /**
     * @param string $key
     *
     * @return Header
     * @throws ReferencedObjectNotExistsException
     */
    public function createHeaderReference(string $key): Header
    {
        if (!array_key_exists($key, $this->headers)) {
            throw new ReferencedObjectNotExistsException();
        }
        $to = '#/components/headers/' . $key;
        return Header::createReference($to);
    }

    /**
     * @param string         $key
     * @param SecurityScheme $securityScheme
     *
     * @return $this
     */
    public function addSecurityScheme(string $key, SecurityScheme $securityScheme): Components
    {
        if ($this->securitySchemes === null) {
            $this->securitySchemes = [];
        }
        $this->securitySchemes[$key] = $securityScheme;
        return $this;
    }

    /**
     * @param string $key
     *
     * @return SecurityScheme
     * @throws ReferencedObjectNotExistsException
     */
    public function createSecuritySchemeReference(string $key): SecurityScheme
    {
        if (!array_key_exists($key, $this->securitySchemes)) {
            throw new ReferencedObjectNotExistsException();
        }
        $to = '#/components/securitySchemes/' . $key;
        return SecurityScheme::createReference($to);
    }

    /**
     * @param string $key
     * @param Link   $link
     *
     * @return $this
     */
    public function addLinks(string $key, Link $link): Components
    {
        if ($this->links === null) {
            $this->links = [];
        }
        $this->links[$key] = $link;
        return $this;
    }

    /**
     * @param string $key
     *
     * @return Link
     * @throws ReferencedObjectNotExistsException
     */
    public function createLinkReference(string $key): Link
    {
        if (!array_key_exists($key, $this->links)) {
            throw new ReferencedObjectNotExistsException();
        }
        $to = '#/components/links/' . $key;
        return Link::createReference($to);
    }

    /**
     * @param string   $key
     * @param Callback $callback
     *
     * @return $this
     */
    public function addCallback(string $key, Callback $callback): Components
    {
        if ($this->callbacks === null) {
            $this->callbacks = [];
        }
        $this->callbacks[$key] = $callback;
        return $this;
    }

    /**
     * @param string $key
     *
     * @return Callback
     * @throws ReferencedObjectNotExistsException
     */
    public function createCallbackReference(string $key): Callback
    {
        if (!array_key_exists($key, $this->callbacks)) {
            throw new ReferencedObjectNotExistsException();
        }
        $to = '#/components/callbacks/' . $key;
        return Callback::createReference($to);
    }

    public function jsonSerialize()
    {
        $ret = [];
        if ($this->schemas) {
            $ret['schemas'] = $this->schemas;
        }
        if ($this->responses) {
            $ret['responses'] = $this->responses;
        }
        if ($this->parameters) {
            $ret['parameters'] = $this->parameters;
        }
        if ($this->examples) {
            $ret['examples'] = $this->examples;
        }
        if ($this->requestBodies) {
            $ret['requestBodies'] = $this->requestBodies;
        }
        if ($this->headers) {
            $ret['headers'] = $this->headers;
        }
        if ($this->securitySchemes) {
            $ret['securitySchemes'] = $this->securitySchemes;
        }
        if ($this->links) {
            $ret['links'] = $this->links;
        }
        if ($this->callbacks) {
            $ret['callbacks'] = $this->callbacks;
        }
        return (object)$ret;
    }
}
