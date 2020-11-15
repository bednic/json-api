<?php

declare(strict_types=1);

namespace JSONAPI\URI\Path;

use JSONAPI\Exception\Metadata\MetadataException;
use JSONAPI\URI\QueryPartInterface;

/**
 * Interface PathInterface
 *
 * @package JSONAPI\URI\Path
 */
interface PathInterface extends QueryPartInterface
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
