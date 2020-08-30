<?php

declare(strict_types=1);

namespace JSONAPI\Uri\Filtering;

use JSONAPI\Uri\QueryPartInterface;

/**
 * Interface FilterInterface
 *
 * @package JSONAPI\Uri\Filtering
 */
interface FilterInterface extends QueryPartInterface
{

    /**
     * @return mixed
     */
    public function getCondition();
}
