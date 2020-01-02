<?php

namespace JSONAPI\Uri\Path;

use Fig\Http\Message\RequestMethodInterface;
use JSONAPI\Exception\Driver\DriverException;
use JSONAPI\Exception\Http\BadRequest;
use JSONAPI\Exception\InvalidArgumentException;
use JSONAPI\Exception\Metadata\MetadataException;
use JSONAPI\Metadata\MetadataFactory;

/**
 * Class PathParser
 *
 * @package JSONAPI\Uri\Path
 */
class PathParser implements PathInterface
{
    /**
     * @var MetadataFactory
     */
    private MetadataFactory $factory;

    /**
     * @var string
     */
    private string $resource = '';

    /**
     * @var string|null
     */
    private ?string $id = null;

    /**
     * @var string|null
     */
    private ?string $relationshipField = null;

    /**
     * @var bool
     */
    private bool $isRelationship = false;

    /**
     * @var string
     */
    private string $method;

    /**
     * PathParser constructor.
     *
     * @param MetadataFactory $metadataFactory
     */
    public function __construct(MetadataFactory $metadataFactory)
    {
        $this->factory = $metadataFactory;
    }

    /**
     * @param string $data
     * @param string $method
     *
     * @return PathInterface
     * @throws BadRequest
     */
    public function parse(string $data, string $method): PathInterface
    {

        if (
            !in_array(
                $method,
                [
                RequestMethodInterface::METHOD_GET,
                RequestMethodInterface::METHOD_POST,
                RequestMethodInterface::METHOD_PUT,
                RequestMethodInterface::METHOD_PATCH
                ]
            )
        ) {
            throw new BadRequest("Request method $method is not supported.");
        }
        $this->method = $method;
        $pattern = '/(?P<resource>[a-zA-Z0-9-_]+)(\/(?P<id>[a-zA-Z0-9-_]+))?'
            . '((\/relationships\/(?P<relationship>[a-zA-Z0-9-_]+))|(\/(?P<related>[a-zA-Z0-9-_]+)))?$/';

        if (preg_match($pattern, $data, $matches)) {
            $this->resource = $matches['resource'];
            $this->id = isset($matches['id']) ? $matches['id'] : null;
            if (isset($matches['relationship']) && strlen($matches['relationship']) > 0) {
                $this->isRelationship = true;
                $this->relationshipField = $matches['relationship'];
            } elseif (isset($matches['related']) && strlen($matches['related']) > 0) {
                $this->isRelationship = false;
                $this->relationshipField = $matches['related'];
            }
        } else {
            throw new BadRequest("Invalid URL");
        }
        return $this;
    }

    public function getResourceType(): string
    {
        return $this->resource;
    }

    /**
     * @return string
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @return string
     * @throws DriverException
     * @throws InvalidArgumentException
     * @throws MetadataException
     */
    public function getRelationshipType(): ?string
    {
        if ($this->relationshipField) {
            return $this->factory
                ->getMetadataByClass(
                    $this->factory
                        ->getMetadataClassByType($this->resource)
                        ->getRelationship($this->relationshipField)
                        ->target
                )
                ->getResource()
                ->type;
        }
        return null;
    }

    /**
     * @return bool
     */
    public function isRelationship(): bool
    {
        return $this->isRelationship;
    }

    /**
     * @return string
     * @throws DriverException
     * @throws InvalidArgumentException
     * @throws MetadataException
     */
    public function getPrimaryResourceType(): string
    {
        return $this->getRelationshipType() ?? $this->factory->getMetadataClassByType(
            $this->getResourceType()
        )->getResource()->type;
    }

    /**
     * Method returns if endpoint represents collection
     *
     * @return bool
     * @throws DriverException
     * @throws InvalidArgumentException
     * @throws MetadataException
     */
    public function isCollection(): bool
    {
        if ($this->getRelationshipType()) {
            return $this->factory
                ->getMetadataClassByType($this->getResourceType())
                ->getRelationship($this->getRelationshipType())
                ->isCollection;
        }
        if ($this->getId()) {
            return false;
        }
        if ($this->method === RequestMethodInterface::METHOD_POST) {
            return false;
        }
        return true;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        $str = '/' . $this->resource;
        if ($this->id) {
            $str .= '/' . $this->id;
            if ($this->relationshipField) {
                if ($this->isRelationship) {
                    $str .= '/relationships';
                }
                $str .= '/' . $this->relationshipField;
            }
        }
        return $str;
    }
}
