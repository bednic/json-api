<?php

declare(strict_types=1);

namespace JSONAPI\Uri\Pagination;

/**
 * Class LimitOffsetPagination
 *
 * @package JSONAPI\Uri\Pagination
 */
class LimitOffsetPagination implements PaginationInterface, PaginationParserInterface, UseTotalCount
{
    public const PAGE_START_KEY = 'offset';
    public const PAGE_SIZE_KEY  = 'limit';

    /**
     * @var int
     */
    private int $defaultOffset;

    /**
     * @var int
     */
    private int $defaultLimit;

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
        $this->defaultOffset = $this->offset = $offset;
        $this->defaultLimit  = $this->limit = $limit;
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
     * @param array|null $data
     *
     * @return PaginationInterface
     */
    public function parse(?array $data): PaginationInterface
    {
        $this->limit  = $this->defaultLimit;
        $this->offset = $this->defaultOffset;
        if ($data) {
            if (isset($data[self::PAGE_SIZE_KEY])) {
                $this->limit = filter_var($data[self::PAGE_SIZE_KEY], FILTER_VALIDATE_INT, [
                    'options' => [
                        'default' => $this->defaultLimit
                    ]
                ]);
            }

            if (isset($data[self::PAGE_START_KEY])) {
                $this->offset = filter_var($data[self::PAGE_START_KEY], FILTER_VALIDATE_INT, [
                    'options' => [
                        'default' => $this->defaultOffset
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
                $static = new self($this->getOffset() + $this->getLimit(), $this->getLimit());
                $static->setTotal($this->total);
            }
        } else {
            $static = new self($this->getOffset() + $this->getLimit(), $this->getLimit());
        }
        return $static;
    }

    /**
     * @return LimitOffsetPagination|null
     */
    public function prev(): ?PaginationInterface
    {
        if ($this->getOffset() - $this->getLimit() >= 0) {
            $static = new self($this->getOffset() - $this->getLimit(), $this->getLimit());
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
        $static = new self(0, $this->getLimit());
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
            $static = new self(max(0, $this->total - $this->getLimit()), $this->getLimit());
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
        return rawurlencode('page[' . self::PAGE_START_KEY . ']') . '=' . $this->getOffset()
            . '&' . rawurlencode('page[' . self::PAGE_SIZE_KEY . ']') . '=' . $this->getLimit();
    }
}
