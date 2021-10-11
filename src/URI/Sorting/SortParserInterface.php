<?php

/**
 * Created by lasicka@logio.cz
 * at 06.10.2021 13:15
 */

declare(strict_types=1);

namespace JSONAPI\URI\Sorting;

use JSONAPI\Exception\Http\BadRequest;

/**
 * Interface SortParserInterface
 *
 * @package JSONAPI\URI\Sorting
 */
interface SortParserInterface
{
    /**
     * @param string|null $data
     *
     * @return SortInterface
     * @throws BadRequest
     */
    public function parse(?string $data): SortInterface;
}
