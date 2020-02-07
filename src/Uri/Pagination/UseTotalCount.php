<?php

declare(strict_types=1);

namespace JSONAPI\Uri\Pagination;

/**
 * Interface UseTotalCount
 *
 * @package JSONAPI\Uri\Pagination
 */
interface UseTotalCount
{
    /**
     * @param int $total
     */
    public function setTotal(int $total): void;
}
