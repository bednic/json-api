<?php

namespace JSONAPI\Uri;

use JSONAPI\Exception\Http\MethodNotImplemented;

/**
 * Interface Pagination
 *
 * @package JSONAPI\Query
 */
interface Pagination
{

    /**
     * @return int
     * @throws MethodNotImplemented
     */
    public function getCursor(): int;

    /**
     * @return int
     * @throws MethodNotImplemented
     */
    public function getNumber(): int;

    /**
     * @return int
     * @throws MethodNotImplemented
     */
    public function getSize(): int;

    /**
     * @return int
     * @throws MethodNotImplemented
     */
    public function getLimit(): int;

    /**
     * @return int
     * @throws MethodNotImplemented
     */
    public function getOffset(): int;
}
