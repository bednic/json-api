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
     * @var mixed
     */
    private $filter;

    /**
     * Function accepts value from filter query param and returns whatever you need
     *
     * @param mixed $filter
     */
    public function parse($filter)
    {
        $this->filter = $filter;
    }

    /**
     * @return mixed
     */
    public function getCondition()
    {
        return $this->filter;
    }
}
