<?php

namespace JSONAPI\Uri\Inclusion;

use JSONAPI\Uri\UriPartInterface;

/**
 * Interface InclusionInterface
 *
 * @package JSONAPI\Uri\Inclusion
 */
interface InclusionInterface extends UriPartInterface
{
    /**
     * @return Inclusion[]
     */
    public function getInclusions(): array;

    /**
     * @return bool
     */
    public function hasInclusions(): bool;
}
