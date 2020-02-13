<?php

declare(strict_types=1);

namespace JSONAPI\Uri\Path;

use JSONAPI\Exception\Http\BadRequest;

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
     * PathParser constructor.
     */
    public function __construct()
    {
    }

    /**
     * @param string $data
     *
     * @return PathInterface
     * @throws BadRequest
     */
    public function parse(string $data): PathInterface
    {
        $resourceKey = 'resource';
        $idKey = 'id';
        $relationshipKey = 'relationship';
        $relatedKey = 'related';
        $pattern = '/(?P<resource>[a-zA-Z0-9-_]+)(\/(?P<id>[a-zA-Z0-9-_]+))?'
            . '((\/relationships\/(?P<relationship>[a-zA-Z0-9-_]+))|(\/(?P<related>[a-zA-Z0-9-_]+)))?$/';

        if (preg_match($pattern, $data, $matches)) {
            $this->resource = $matches[$resourceKey];
            $this->id = isset($matches[$idKey]) ? $matches[$idKey] : null;
            if (isset($matches[$relationshipKey]) && strlen($matches[$relationshipKey]) > 0) {
                $this->isRelationship = true;
                $this->relationship = $matches[$relationshipKey];
            } elseif (isset($matches[$relatedKey]) && strlen($matches[$relatedKey]) > 0) {
                $this->isRelationship = false;
                $this->relationship = $matches[$relatedKey];
            }
        } else {
            throw new BadRequest("Invalid URL");
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
}
