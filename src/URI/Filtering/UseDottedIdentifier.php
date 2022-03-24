<?php

declare(strict_types=1);

namespace JSONAPI\URI\Filtering;

use JSONAPI\Data\Collection;

/**
 * Interface UseDottedIdentifier
 *
 * @package JSONAPI\URI\Filtering\Builder
 */
interface UseDottedIdentifier
{
    /**
     * @return Collection<string, string>
     */
    public function getRequiredJoins(): Collection;
}
