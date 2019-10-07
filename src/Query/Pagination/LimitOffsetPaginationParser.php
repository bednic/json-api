<?php

namespace JSONAPI\Query\Pagination;

use JSONAPI\Exception\Query\MethodNotImplemented;
use JSONAPI\Query\Pagination;

/**
 * Class LimitOffsetPaginationParser
 *
 * @package JSONAPI\Query
 */
class LimitOffsetPaginationParser implements Pagination
{

    /**
     * @var int
     */
    private $limit;

    /**
     * @var int
     */
    private $offset;

    /**
     * LimitOffsetPaginationParser constructor.
     *
     * @param int $limit
     * @param int $offset
     */
    public function __construct(int $limit = 25, int $offset = 0)
    {
        $this->limit = $limit;
        $this->offset = $offset;
    }

    /**
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * @return int
     */
    public function getOffset(): int
    {
        return $this->offset;
    }

    /**
     * @param array $data
     */
    public function parse($data): void
    {
        if (isset($data['limit'])) {
            $this->limit = filter_var($data['limit'], FILTER_VALIDATE_INT);
        }

        if (isset($data['offset'])) {
            $this->offset = filter_var($data['offset'], FILTER_VALIDATE_INT);
        }
    }

    /**
     * @return int
     * @throws MethodNotImplemented
     */
    public function getCursor(): int
    {
        throw new MethodNotImplemented();
    }

    /**
     * @return int
     * @throws MethodNotImplemented
     */
    public function getNumber(): int
    {
        throw new MethodNotImplemented();
    }

    /**
     * @return int
     * @throws MethodNotImplemented
     */
    public function getSize(): int
    {
        throw new MethodNotImplemented();
    }
}
