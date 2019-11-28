<?php

namespace JSONAPI\Uri;

/**
 * Interface Filter
 *
 * @package JSONAPI\Query
 */
interface Filter
{

    /**
     * @return mixed
     */
    public function getCondition();
}
