<?php

declare(strict_types=1);

namespace JSONAPI\URI\Filtering;

use JSONAPI\Exception\Http\BadRequest;
use JSONAPI\URI\Path\PathInterface;

/**
 * Interface FilterParserInterface
 *
 * @package JSONAPI\URI\Filtering
 */
interface FilterParserInterface
{
    /**
     * @param mixed         $data
     * @param PathInterface $path
     *
     * @return FilterInterface
     * @throws BadRequest
     */
    public function parse(mixed $data, PathInterface $path): FilterInterface;
}
