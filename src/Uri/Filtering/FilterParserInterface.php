<?php

declare(strict_types=1);

namespace JSONAPI\Uri\Filtering;

use JSONAPI\Exception\Http\BadRequest;

interface FilterParserInterface
{
    /**
     * @param string|array|null $data
     *
     * @return FilterInterface
     * @throws BadRequest
     */
    public function parse($data): FilterInterface;
}
