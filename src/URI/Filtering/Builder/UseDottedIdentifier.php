<?php

declare(strict_types=1);

namespace JSONAPI\URI\Filtering\Builder;

/**
 * Interface UseDottedIdentifier
 *
 * @package JSONAPI\URI\Filtering\Builder
 */
interface UseDottedIdentifier
{

    /**
     * @param string $identifier
     *
     * @return mixed
     */
    public function parseIdentifier(string $identifier);

    /**
     * @return array
     */
    public function getRequiredJoins(): array;
}
