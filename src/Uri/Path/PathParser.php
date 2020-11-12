<?php

declare(strict_types=1);

namespace JSONAPI\Uri\Path;

use Fig\Http\Message\RequestMethodInterface;
use JSONAPI\Exception\Http\BadRequest;
use JSONAPI\Metadata\MetadataRepository;

/**
 * Class PathParser
 *
 * @package JSONAPI\Uri\Path
 */
class PathParser implements PathInterface
{
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
    private ?string $relationship = null;

    /**
     * @var bool
     */
    private bool $isRelationship = false;
    /**
     * @var MetadataRepository
     */
    private MetadataRepository $metadataRepository;
    /**
     * @var string
     */
    private string $method;
    private string $baseURL;

    /**
     * PathParser constructor.
     *
     * @param MetadataRepository $metadataRepository
     * @param string             $baseURL
     * @param string             $method
     */
    public function __construct(
        MetadataRepository $metadataRepository,
        string $baseURL,
        string $method = RequestMethodInterface::METHOD_GET
    ) {
        $this->metadataRepository = $metadataRepository;
        $this->baseURL            = $baseURL;
        $this->method             = $method;
    }

    /**
     * @param string $data
     *
     * @return PathInterface
     * @throws BadRequest
     */
    public function parse(string $data): PathInterface
    {

        $req             = explode('/', $data);
        $base            = explode('/', parse_url($this->baseURL, PHP_URL_PATH) ?? '');
        $diff            = array_diff($req, $base);
        $data            = implode('/', $diff);
        $resourceKey     = 'resource';
        $idKey           = 'id';
        $relationshipKey = 'relationship';
        $relatedKey      = 'related';
        $pattern         = '/(?P<resource>[a-zA-Z0-9-_]+)(\/(?P<id>[a-zA-Z0-9-_]+))?'
            . '((\/relationships\/(?P<relationship>[a-zA-Z0-9-_]+))|(\/(?P<related>[a-zA-Z0-9-_]+)))?$/';

        if (preg_match($pattern, $data, $matches) !== false) {
            $this->resource = $matches[$resourceKey];
            $this->id       = isset($matches[$idKey]) ? $matches[$idKey] : null;
            if (isset($matches[$relationshipKey]) && strlen($matches[$relationshipKey]) > 0) {
                $this->isRelationship = true;
                $this->relationship   = $matches[$relationshipKey];
            } elseif (isset($matches[$relatedKey]) && strlen($matches[$relatedKey]) > 0) {
                $this->isRelationship = false;
                $this->relationship   = $matches[$relatedKey];
            }
        } else {
            throw new BadRequest();
        }
        return $this;
    }

    /**
     * @return string
     */
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
     * @return bool
     */
    public function isRelationship(): bool
    {
        return $this->isRelationship;
    }

    /**
     * Method returns if endpoint represents collection
     *
     * @return string|null
     */
    public function getRelationshipName(): ?string
    {
        return $this->relationship;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        $str = '/' . $this->resource;
        if ($this->id) {
            $str .= '/' . $this->id;
            if ($this->relationship) {
                if ($this->isRelationship) {
                    $str .= '/relationships';
                }
                $str .= '/' . $this->relationship;
            }
        }
        return $str;
    }

    /**
     * @inheritDoc
     */
    public function getPrimaryResourceType(): string
    {
        if ($this->getRelationshipName()) {
            return $this->metadataRepository
                ->getByClass(
                    $this->metadataRepository
                        ->getByType($this->getResourceType())
                        ->getRelationship($this->getRelationshipName())
                        ->target
                )
                ->getType();
        } else {
            return $this->metadataRepository->getByType($this->getResourceType())->getType();
        }
    }

    /**
     * @inheritDoc
     */
    public function isCollection(): bool
    {
        if ($this->getRelationshipName()) {
            return $this->metadataRepository
                ->getByType($this->getResourceType())
                ->getRelationship($this->getRelationshipName())
                ->isCollection;
        }
        if ($this->getId() || (strtoupper($this->method) === RequestMethodInterface::METHOD_POST)) {
            return false;
        }
        return true;
    }
}
