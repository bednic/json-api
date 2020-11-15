<?php

declare(strict_types=1);

namespace JSONAPI\URI\Inclusion;

use JSONAPI\URI\QueryPartInterface;

/**
 * Interface InclusionInterface
 *
 * @package JSONAPI\URI\Inclusion
 */
interface InclusionInterface extends QueryPartInterface
{
    /**
     * @return Inclusion[]
     */
    public function getInclusions(): array;

    /**
     * @return bool
     */
    public function hasInclusions(): bool;

    /**
     * @param string $relation
     *
     * @return bool
     */
    public function contains(string $relation): bool;
}
