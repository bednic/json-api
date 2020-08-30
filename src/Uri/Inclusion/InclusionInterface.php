<?php

declare(strict_types=1);

namespace JSONAPI\Uri\Inclusion;

use JSONAPI\Uri\QueryPartInterface;

/**
 * Interface InclusionInterface
 *
 * @package JSONAPI\Uri\Inclusion
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
