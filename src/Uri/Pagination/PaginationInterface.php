<?php

declare(strict_types=1);

namespace JSONAPI\Uri\Pagination;

use JSONAPI\Uri\UriPartInterface;

interface PaginationInterface extends UriPartInterface
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
