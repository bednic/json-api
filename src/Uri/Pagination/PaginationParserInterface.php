<?php

declare(strict_types=1);

namespace JSONAPI\Uri\Pagination;

/**
 * Interface PaginationParserInterface
 *
 * @package JSONAPI\Uri\Pagination
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
