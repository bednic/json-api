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
    public function getRelationshipType(): ?string;

    /**
     * @return bool
     */
    public function isRelationship(): bool;

    /**
     * Return true if path points to collection
     * @return bool
     */
    public function isCollection(): bool;

    /**
     * Return primary data Resource type
     * @return string
     */
    public function getPrimaryResourceType(): string;
}
