<?php

declare(strict_types=1);

namespace JSONAPI\Uri\Path;

use JSONAPI\Exception\Metadata\MetadataException;
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
     *
     * @return string|null
     */
    public function getRelationshipName(): ?string;

    /**
     * @return bool
     */
    public function isRelationship(): bool;

    /**
     * @return string
     * @throws MetadataException
     */
    public function getPrimaryResourceType(): string;

    /**
     * @return bool
     * @throws MetadataException
     */
    public function isCollection(): bool;
}
