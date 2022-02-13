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
     * @param array<string, int>|null $data
     *
     * @return PaginationInterface
     */
    public function parse(?array $data): PaginationInterface;
}
