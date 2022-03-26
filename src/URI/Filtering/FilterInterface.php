<?php

declare(strict_types=1);

namespace JSONAPI\URI\Filtering;

use JSONAPI\Data\Collection;
use JSONAPI\URI\QueryPartInterface;

/**
 * Interface FilterInterface
 *
 * @package JSONAPI\URI\Filtering
 */
interface FilterInterface extends QueryPartInterface
{
    /**
     * @return mixed
     */
    public function getCondition(): mixed;
}
