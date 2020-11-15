<?php

declare(strict_types=1);

namespace JSONAPI\URI\Pagination;

use JSONAPI\URI\QueryPartInterface;

/**
 * Interface PaginationInterface
 *
 * @package JSONAPI\URI\Pagination
 */
interface PaginationInterface extends QueryPartInterface
{
    /**
     * @return PaginationInterface|null
     */
    public function next(): ?PaginationInterface;

    /**
     * @return PaginationInterface|null
     */
    public function prev(): ?PaginationInterface;

    /**
     * @return PaginationInterface
     */
    public function first(): PaginationInterface;

    /**
     * @return PaginationInterface|null
     */
    public function last(): ?PaginationInterface;
}
