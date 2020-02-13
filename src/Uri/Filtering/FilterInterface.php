<?php

declare(strict_types=1);

namespace JSONAPI\Uri\Filtering;

use JSONAPI\Uri\UriPartInterface;

/**
 * Interface FilterInterface
 *
 * @package JSONAPI\Uri\Filtering
 */
interface FilterInterface extends UriPartInterface
{

    /**
     * @return mixed
     */
    public function getCondition();
}
