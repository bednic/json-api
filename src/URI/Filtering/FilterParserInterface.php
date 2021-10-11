<?php

declare(strict_types=1);

namespace JSONAPI\URI\Filtering;

use JSONAPI\Exception\Http\BadRequest;

/**
 * Interface FilterParserInterface
 *
 * @package JSONAPI\URI\Filtering
 */
interface FilterParserInterface
{
    /**
     * @param mixed $data
     *
     * @return FilterInterface
     * @throws BadRequest
     */
    public function parse(mixed $data): FilterInterface;
}
