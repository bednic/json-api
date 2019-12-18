<?php

namespace JSONAPI\Uri\Pagination;

/**
 * Interface PaginationParserInterface
 *
 * @package JSONAPI\Uri\Pagination
 */
interface PaginationParserInterface
{
    public const KEY = 'page';

    /**
     * @param array $data
     *
     * @return PaginationInterface
     */
    public function parse(array $data): PaginationInterface;
}
