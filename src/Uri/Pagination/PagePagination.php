<?php

namespace JSONAPI\Uri\Pagination;

/**
 * Interface PagePagination
 *
 * @package JSONAPI\Uri\PaginationInterface
 */
class PagePagination implements PaginationInterface, PaginationParserInterface
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
     * @var int
     */
    private int $total;

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
        if ($this->getNumber() + 1 <= $this->total) {
            $static = new static($this->getNumber() + 1, $this->getSize());
            $static->setTotal($this->total);
            return $static;
        }
        return null;
    }

    /**
     * @return PaginationInterface|null
     */
    public function prev(): ?PaginationInterface
    {
        if ($this->getNumber() - 1 > 0) {
            $static = new static($this->getNumber() - 1, $this->getSize());
            $static->setTotal($this->total);
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
        $static->setTotal($this->total);
        return $static;
    }

    /**
     * @return PaginationInterface
     */
    public function last(): PaginationInterface
    {
        $static = new static($this->total, $this->getSize());
        $static->setTotal($this->total);
        return $static;
    }

    /**
     * @param array $data
     *
     * @return PaginationInterface
     */
    public function parse(array $data): PaginationInterface
    {
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
        return $this;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return urlencode('page[' . self::PAGE_NUMBER_KEY . ']=' . $this->getNumber()
            . '&page[' . self::PAGE_SIZE_KEY . ']=' . $this->getSize());
    }
}
