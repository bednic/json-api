<?php

declare(strict_types=1);

namespace JSONAPI\Uri\Pagination;

/**
 * Class CursorPagination
 * Class is abstract because you should implement interface methods based on you cursor mechanics
 *
 * @package JSONAPI\Uri\Pagination
 */
abstract class CursorPagination implements PaginationInterface, PaginationParserInterface
{
    /**
     * @var string
     */
    private string $cursor;

    /**
     * @return mixed
     */
    abstract public function getCursor(): string;

    /**
     * @param array $data
     *
     * @return PaginationInterface
     */
    public function parse(?array $data): PaginationInterface
    {
        if ($data && isset($data['cursor'])) {
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
