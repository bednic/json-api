<?php

/**
 * Created by lasicka@logio.cz
 * at 06.10.2021 13:11
 */

declare(strict_types=1);

namespace JSONAPI\URI\Fieldset;

use JSONAPI\Exception\Http\BadRequest;

interface FieldsetParserInterface
{
    /**
     * @param array|null $data
     *
     * @return FieldsetInterface
     * @throws BadRequest
     */
    public function parse(?array $data): FieldsetInterface;
}
