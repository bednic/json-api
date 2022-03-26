<?php

declare(strict_types=1);

namespace JSONAPI\URI\Path;

use Fig\Http\Message\RequestMethodInterface;
use JSONAPI\Document\KeyWord;
use JSONAPI\Exception\Http\BadRequest;
use JSONAPI\Metadata\MetadataRepository;

/**
 * Class PathParser
 *
 * @package JSONAPI\URI\Path
 */
class PathParser implements PathInterface, PathParserInterface
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
    private string $method = RequestMethodInterface::METHOD_GET;
    /**
     * @var string baseURL
     */
    private string $baseURL;

    /**
     * PathParser constructor.
     *
     * @param MetadataRepository $metadataRepository
     * @param string             $baseURL
     */
    public function __construct(
        MetadataRepository $metadataRepository,
        string $baseURL
    ) {
        $this->metadataRepository = $metadataRepository;
        $this->baseURL            = $baseURL;
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
        $this->method         = $method;
        $this->relationship   = null;
        $this->isRelationship = false;
        $req                  = explode('/', $data);
        $base                 = explode('/', parse_url($this->baseURL, PHP_URL_PATH) ?? '');
        $diff                 = array_diff($req, $base);
        $data                 = implode('/', $diff);
        $pattern              = '/(?P<resource>[a-zA-Z0-9-_]+)(\/(?P<id>[a-zA-Z0-9-_]+))?'
            . '((\/relationships\/(?P<relationship>[a-zA-Z0-9-_]+))|(\/(?P<related>[a-zA-Z0-9-_]+)))?$/';

        if (preg_match($pattern, $data, $matches)) {
            foreach (['resource', 'id', 'relationship', 'related'] as $key) {
                if (isset($matches[$key]) && strlen($matches[$key]) > 0) {
                    $this->$$key = $matches[$key];
                    if ($key === 'relationship') {
                        $this->isRelationship = true;
                    }
                }
            }
        } else {
            throw new BadRequest();
        }
        return $this;
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
    public function getResourceType(): string
    {
        return $this->resource;
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

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }
}
