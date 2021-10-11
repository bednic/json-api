<?php

/**
 * Created by lasicka@logio.cz
 * at 06.10.2021 13:14
 */

declare(strict_types=1);

namespace JSONAPI\URI\Path;

use JSONAPI\Exception\Http\BadRequest;

/**
 * Interface PathParserInterface
 *
 * @package JSONAPI\URI\Path
 */
interface PathParserInterface
{
    /**
     * @param string $data
     * @param string $method
     *
     * @return PathInterface
     * @throws BadRequest
     */
    public function parse(string $data, string $method): PathInterface;
}
