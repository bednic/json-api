<?php

/**
 * Created by lasicka@logio.cz
 * at 06.10.2021 13:13
 */

declare(strict_types=1);

namespace JSONAPI\URI\Inclusion;

use JSONAPI\Exception\Http\BadRequest;

interface InclusionParserInterface
{
    /**
     * @param string|null $data
     *
     * @return InclusionInterface
     * @throws BadRequest
     */
    public function parse(?string $data): InclusionInterface;
}
