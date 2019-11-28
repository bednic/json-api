<?php


namespace JSONAPI\Uri\Path;


use JSONAPI\Exception\Http\BadRequest;
use JSONAPI\Exception\InvalidArgumentException;
use JSONAPI\Metadata\MetadataFactory;
use JSONAPI\Uri\UriParser;

class PathParser implements UriParser
{
    /**
     * @var MetadataFactory
     */
    private $factory;
    /**
     * @var string
     */
    private $resource;
    /**
     * @var string
     */
    private $id;
    /**
     * @var string
     */
    private $relation;
    /**
     * @var bool
     */
    private $relationship;

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
     * @param $data
     *
     * @throws BadRequest
     * @throws InvalidArgumentException
     */
    public function parse($data): void
    {
        if (!is_string($data)) {
            throw new InvalidArgumentException("Parameter query must be a string.");
        }
        $pattern = '/^\/(?P<resource>[a-zA-Z0-9-_]+)(\/(?P<id>[a-zA-Z0-9-_]+))?'
            . '((\/relationships\/(?P<relationship>[a-zA-Z0-9-_]+))|(\/(?P<related>[a-zA-Z0-9-_]+)))?$/';
        if (preg_match($pattern, $data, $matches)) {
            $this->resource = $matches['resource'];
            $this->id = isset($matches['id']) ? $matches['id'] : null;
            if (isset($matches['relationship'])) {
                $this->relationship = true;
                $this->relation = $matches['relationship'];
            } elseif (isset($matches['related'])) {
                $this->relationship = false;
                $this->relation = $matches['related'];
            }
        } else {
            throw new BadRequest("Invalid URL");
        }
    }

    public function getResource(): string
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
     */
    public function getRelation(): ?string
    {
        return $this->relation;
    }

    /**
     * @return bool
     */
    public function isRelationship(): bool
    {
        return $this->relationship;
    }

    /**
     * @return string
     * @throws \JSONAPI\Exception\Driver\AnnotationMisplace
     * @throws \JSONAPI\Exception\Driver\ClassNotExist
     * @throws \JSONAPI\Exception\Driver\ClassNotResource
     * @throws \JSONAPI\Exception\Driver\DriverException
     */
    public function getPrimaryDataType(): string
    {
        $metadata = $this->factory->getMetadataClassByType($this->getResource());
        if ($name = $this->getRelation()) {
            return $this->factory->getMetadataByClass($metadata->getRelationship($name)->target)->getResource()->type;
        }
        return $metadata->getResource()->type;
    }

    /**
     * Method returns if endpoint represents collection
     *
     * @return bool
     * @throws \JSONAPI\Exception\Driver\AnnotationMisplace
     * @throws \JSONAPI\Exception\Driver\ClassNotExist
     * @throws \JSONAPI\Exception\Driver\ClassNotResource
     * @throws \JSONAPI\Exception\Driver\DriverException
     */
    public function isCollection(): bool
    {
        if (!empty($this->getRelation())) {
            return $this->factory
                ->getMetadataClassByType($this->getResource())
                ->getRelationship($this->getRelation())
                ->isCollection;
        }
        if (!empty($this->getId())) {
            return false;
        }
        return true;
    }

    public function __toString()
    {
        $str = '/' . $this->resource;
        if ($this->id) {
            $str .= '/' . $this->id;
            if ($this->relation) {
                if ($this->relationship) {
                    $str .= '/relationships';
                }
                $str .= '/' . $this->relation;
            }
        }
        return $str;
    }
}
