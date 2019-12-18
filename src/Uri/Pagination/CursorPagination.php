<?php

namespace JSONAPI\Uri\Pagination;

/**
 * Class CursorPagination
 *
 * Class is abstract because you should implement interface methods based on you cursor mechanics
 *
 * @package JSONAPI\Uri\PaginationInterface
 */
abstract class CursorPagination implements PaginationInterface
{
    /**
     * @var string
     */
    private string $cursor;

    /**
     * @return mixed
     */
    abstract public function getCursor(): string;

    public function setTotal(int $total): void
    {
        // does nothing, cause cursor doesn't use total
    }

    /**
     * @param array $data
     *
     * @return PaginationInterface
     */
    public function parse(array $data): PaginationInterface
    {
        if (isset($data['cursor'])) {
            $this->cursor = $data['cursor'];
        }
        return $this;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return urlencode('page[cursor]=' . $this->getCursor());
    }
}
