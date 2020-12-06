<?php

declare(strict_types=1);

namespace JSONAPI\URI\Pagination;

/**
 * Class CursorPagination
 * Class is abstract because you should implement interface methods based on you cursor mechanics
 *
 * @package JSONAPI\URI\Pagination
 */
abstract class CursorPagination implements PaginationInterface, PaginationParserInterface
{
    /**
     * @var string
     */
    private string $cursor;

    /**
     * @param array|null $data
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

    /**
     * @return string
     */
    public function getCursor(): string
    {
        return $this->cursor;
    }
}
