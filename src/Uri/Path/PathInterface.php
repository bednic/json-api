<?php

namespace JSONAPI\Uri\Path;

use JSONAPI\Uri\UriPartInterface;

/**
 * Interface PathInterface
 *
 * @package JSONAPI\Uri\PathInterface
 */
interface PathInterface extends UriPartInterface
{

    /**
     * @return string
     */
    public function getResourceType(): string;

    /**
     * @return string|null
     */
    public function getId(): ?string;

    /**
     * Returns field of relationship
     * @return string|null
     */
    public function getRelationshipName(): ?string;

    /**
     * @return bool
     */
    public function isRelationship(): bool;
}
