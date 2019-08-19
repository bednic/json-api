<?php


namespace JSONAPI\Query;


use JSONAPI\Exception\Query\MethodNotImplemented;

/**
 * Interface Pagination
 *
 * @package JSONAPI\Query
 */
interface Pagination
{
    /**
     * @param mixed $data
     *
     * @return mixed
     */
    public function parse($data);

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
