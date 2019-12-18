<?php

namespace JSONAPI\Uri\Filtering;

use JSONAPI\Exception\Http\BadRequest;

interface FilterParserInterface
{
    /**
     * @param string|array $data
     *
     * @return FilterInterface
     * @throws BadRequest
     */
    public function parse($data): FilterInterface;
}
