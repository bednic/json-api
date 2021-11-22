<?php

declare(strict_types=1);

namespace JSONAPI\OAS;

use JSONAPI\Document\Serializable;
use JSONAPI\Exception\OAS\DuplicationEntryException;
use JSONAPI\Exception\OAS\InvalidArgumentException;
use JSONAPI\Exception\OAS\ReferencedObjectNotExistsException;

/**
 * Class Components
 *
 * @package JSONAPI\OAS
 */
class Components implements Serializable
{
    /**
     * @var Schema[]
     */
    private array $schemas = [];
    /**
     * @var Response[]
     */
    private array $responses = [];
    /**
     * @var Parameter[]
     */
    private array $parameters = [];
    /**
     * @var Example[]
     */
    private array $examples = [];
    /**
     * @var RequestBody[]
     */
    private array $requestBodies = [];
    /**
     * @var Header[]
     */
    private array $headers = [];
    /**
     * @var SecurityScheme[]
     */
    private array $securitySchemes = [];
    /**
     * @var Link[]
     */
    private array $links = [];
    /**
     * @var Callback[]
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
        $origin = $this->schemas[$key];
        return Schema::createReference($to, $origin);
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
        $origin = $this->responses[$key];
        return Response::createReference($to, $origin);
    }

    /**
     * @param string    $key
     * @param Parameter $parameter
     *
     * @return $this
     * @throws InvalidArgumentException
     * @throws DuplicationEntryException
     */
    public function addParameter(string $key, Parameter $parameter): Components
    {
        if (empty($key)) {
            throw new InvalidArgumentException();
        }
        foreach ($this->parameters as $p) {
            if ($parameter->getUID() === $p->getUID()) {
                throw new DuplicationEntryException();
            }
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
        $origin = $this->parameters[$key];
        return Parameter::createReference($to, $origin);
    }

    /**
     * @param string  $key
     * @param Example $example
     *
     * @return Components
     */
    public function addExample(string $key, Example $example): Components
    {
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
        $origin = $this->examples[$key];
        $to = '#/components/examples/' . $key;
        return Example::createReference($to, $origin);
    }

    /**
     * @param string      $key
     * @param RequestBody $requestBody
     *
     * @return $this
     */
    public function addRequestBody(string $key, RequestBody $requestBody): Components
    {
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
        $origin = $this->requestBodies[$key];
        return RequestBody::createReference($to, $origin);
    }

    /**
     * @param Header $header
     *
     * @return $this
     */
    public function addHeader(Header $header): Components
    {
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
        $origin = $this->headers[$key];
        return Header::createReference($to, $origin);
    }

    /**
     * @param string         $key
     * @param SecurityScheme $securityScheme
     *
     * @return $this
     */
    public function addSecurityScheme(string $key, SecurityScheme $securityScheme): Components
    {
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
        $origin = $this->securitySchemes[$key];
        return SecurityScheme::createReference($to, $origin);
    }

    /**
     * @param string $key
     * @param Link   $link
     *
     * @return $this
     */
    public function addLinks(string $key, Link $link): Components
    {
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
        $origin = $this->links[$key];
        return Link::createReference($to, $origin);
    }

    /**
     * @param string   $key
     * @param Callback $callback
     *
     * @return $this
     */
    public function addCallback(string $key, Callback $callback): Components
    {
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
        $origin = $this->callbacks[$key];
        return Callback::createReference($to, $origin);
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
