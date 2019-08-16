<?php


namespace JSONAPI\Query;


interface Filter
{
    /**
     * Function accepts value from filter query param and returns whatever you need
     *
     * @param $filter
     */
    public function parse($filter);

    /**
     * @return mixed
     */
    public function getCondition();
}
