<?php

declare(strict_types=1);

namespace JSONAPI\Uri\Filtering\Builder;

/**
 * Interface UseDottedIdentifier
 *
 * @package JSONAPI\Uri\Filtering\Builder
 */
interface UseDottedIdentifier
{

    /**
     * @param string $identifier
     *
     * @return string
     */
    public function parseIdentifier(string $identifier): string;

    /**
     * @return array
     */
    public function getRequiredJoins(): array;
}
