<?php

namespace JSONAPI\Uri\Pagination;

use JSONAPI\Uri\UriPartInterface;

interface PaginationInterface extends UriPartInterface
{
    /**
     * @param $total
     */
    public function setTotal(int $total): void;

    /**
     * @return PaginationInterface|null
     */
    public function next(): ?PaginationInterface;

    /**
     * @return PaginationInterface|null
     */
    public function prev(): ?PaginationInterface;

    /**
     * @return PaginationInterface|null
     */
    public function first(): ?PaginationInterface;

    /**
     * @return PaginationInterface|null
     */
    public function last(): ?PaginationInterface;
}
