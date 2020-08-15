<?php

declare(strict_types=1);

namespace JSONAPI\Uri\Pagination;

/**
 * Class LimitOffsetPaginationParser
 *
 * @package JSONAPI\Query
 */
class LimitOffsetPagination implements PaginationInterface, PaginationParserInterface, UseTotalCount
{

    /**
     * @var int
     */
    private int $offset;

    /**
     * @var int
     */
    private int $limit;

    /**
     * Total items, e.g. in database. It's used for calculating last
     *
     * @var int|null
     */
    private ?int $total = null;

    /**
     * LimitOffsetPagination constructor.
     *
     * @param int $offset
     * @param int $limit
     */
    public function __construct(int $offset = 0, int $limit = 25)
    {
        $this->offset = $offset;
        $this->limit = $limit;
    }

    /**
     * @return int
     */
    public function getOffset(): int
    {
        return $this->offset;
    }

    /**
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * @param array $data
     *
     * @return PaginationInterface
     */
    public function parse(?array $data): PaginationInterface
    {
        if ($data) {
            if (isset($data['limit'])) {
                $this->limit = filter_var($data['limit'], FILTER_VALIDATE_INT, [
                    'options' => [
                        'default' => $this->limit
                    ]
                ]);
            }

            if (isset($data['offset'])) {
                $this->offset = filter_var($data['offset'], FILTER_VALIDATE_INT, [
                    'options' => [
                        'default' => $this->offset
                    ]
                ]);
            }
        }
        return $this;
    }

    /**
     * @param int $total
     */
    public function setTotal(int $total): void
    {
        $this->total = $total;
    }

    /**
     * @return LimitOffsetPagination|null
     */
    public function next(): ?PaginationInterface
    {
        $static = null;
        if ($this->total !== null) {
            if ($this->getOffset() + $this->getLimit() < $this->total) {
                $static = new static($this->getOffset() + $this->getLimit(), $this->getLimit());
                $static->setTotal($this->total);
            }
        } else {
            $static = new static($this->getOffset() + $this->getLimit(), $this->getLimit());
        }
        return $static;
    }

    /**
     * @return LimitOffsetPagination|null
     */
    public function prev(): ?PaginationInterface
    {
        if ($this->getOffset() - $this->getLimit() >= 0) {
            $static = new static($this->getOffset() - $this->getLimit(), $this->getLimit());
            if ($this->total !== null) {
                $static->setTotal($this->total);
            }
            return $static;
        }
        return null;
    }

    /**
     * @return PaginationInterface
     */
    public function first(): PaginationInterface
    {
        $static = new static(0, $this->getLimit());
        if ($this->total !== null) {
            $static->setTotal($this->total);
        }
        return $static;
    }

    /**
     * @return PaginationInterface|null
     */
    public function last(): ?PaginationInterface
    {
        if ($this->total !== null) {
            $static = new static(max(0, $this->total - $this->getLimit()), $this->getLimit());
            $static->setTotal($this->total);
            return $static;
        }
        return null;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return rawurlencode('page[offset]') . '=' . $this->getOffset() . '&' . rawurlencode('page[limit]')
            . '=' . $this->getLimit();
    }
}
