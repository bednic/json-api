<?php

declare(strict_types=1);

namespace JSONAPI\Uri\Pagination;

/**
 * Interface PagePagination
 *
 * @package JSONAPI\Uri\PaginationInterface
 */
class PagePagination implements PaginationInterface, PaginationParserInterface, UseTotalCount
{
    public const PAGE_NUMBER_KEY = 'number';
    public const PAGE_SIZE_KEY = 'size';
    /**
     * @var int
     */
    private int $number;

    /**
     * @var int
     */
    private int $size;

    /**
     * Total pages count
     *
     * @var int|null
     */
    private ?int $total = null;

    /**
     * PagePagination constructor.
     *
     * @param int $number
     * @param int $size
     */
    public function __construct(int $number = 1, int $size = 25)
    {
        $this->number = $number;
        $this->size = $size;
    }

    /**
     * Sets total pages count
     *
     * @param int $total
     */
    public function setTotal(int $total): void
    {
        $this->total = $total;
    }

    /**
     * @return int|null
     */
    public function getNumber(): int
    {
        return $this->number;
    }

    /**
     * @return int|null
     */
    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * @return PaginationInterface|null
     */
    public function next(): ?PaginationInterface
    {
        $static = null;
        if ($this->total !== null) {
            if ($this->getNumber() + 1 <= $this->total) {
                $static = new static($this->getNumber() + 1, $this->getSize());
                $static->setTotal($this->total);
            }
        } else {
            $static = new static($this->getNumber() + 1, $this->getSize());
        }
        return $static;
    }

    /**
     * @return PaginationInterface|null
     */
    public function prev(): ?PaginationInterface
    {
        if ($this->getNumber() - 1 > 0) {
            $static = new static($this->getNumber() - 1, $this->getSize());
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
        $static = new static(1, $this->getSize());
        if ($this->total !== null) {
            $static->setTotal($this->total);
        }
        return $static;
    }

    /**
     * @return PaginationInterface
     */
    public function last(): ?PaginationInterface
    {
        if ($this->total !== null) {
            $static = new static($this->total, $this->getSize());
            if ($this->total !== null) {
                $static->setTotal($this->total);
            }
            return $static;
        }
        return null;
    }

    /**
     * @param array $data
     *
     * @return PaginationInterface
     */
    public function parse(?array $data): PaginationInterface
    {
        if ($data) {
            if (isset($data[self::PAGE_NUMBER_KEY])) {
                $this->number = filter_var($data[self::PAGE_NUMBER_KEY], FILTER_VALIDATE_INT, [
                    'options' => [
                        'default' => $this->number
                    ]
                ]);
            }

            if (isset($data[self::PAGE_SIZE_KEY])) {
                $this->size = filter_var($data[self::PAGE_SIZE_KEY], FILTER_VALIDATE_INT, [
                    'options' => [
                        'default' => $this->size
                    ]
                ]);
            }
        }
        return $this;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return rawurlencode('page[' . self::PAGE_NUMBER_KEY . ']') . '=' . $this->getNumber()
            . '&' . rawurlencode('page[' . self::PAGE_SIZE_KEY . ']') . '=' . $this->getSize();
    }
}
