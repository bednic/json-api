<?php

declare(strict_types=1);

namespace JSONAPI\URI\Pagination;

/**
 * Interface UseTotalCount
 *
 * @package JSONAPI\URI\Pagination
 */
interface UseTotalCount
{
    /**
     * @param int $total
     */
    public function setTotal(int $total): void;
}
