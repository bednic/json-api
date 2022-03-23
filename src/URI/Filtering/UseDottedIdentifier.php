<?php

declare(strict_types=1);

namespace JSONAPI\URI\Filtering;

/**
 * Interface UseDottedIdentifier
 *
 * @package JSONAPI\URI\Filtering\Builder
 */
interface UseDottedIdentifier
{
    /**
     * @return array<string, string>
     */
    public function getRequiredJoins(): array;
}
