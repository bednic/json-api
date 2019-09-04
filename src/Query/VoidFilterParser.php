<?php


namespace JSONAPI\Query;

/**
 * Class VoidFilterParser
 *
 * @package JSONAPI\Query
 */
class VoidFilterParser implements Filter
{
    /**
     * @var string
     */
    private $filter = '';

    /**
     * Function accepts value from filter query param and returns whatever you need
     *
     * @param mixed $filter
     */
    public function parse($filter): void
    {
        $this->filter = $filter;
    }

    /**
     * @return string
     */
    public function getCondition(): string
    {
        return $this->filter;
    }
}
