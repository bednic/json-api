<?php

declare(strict_types=1);

namespace JSONAPI\URI\Pagination;

/**
 * Interface PaginationParserInterface
 *
 * @package JSONAPI\URI\Pagination
 */
interface PaginationParserInterface
{
    /**
     * @param array|null $data
     *
     * @return PaginationInterface
     */
    public function parse(?array $data): PaginationInterface;
}
