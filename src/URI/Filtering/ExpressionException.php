<?php

declare(strict_types=1);

namespace JSONAPI\URI\Filtering;

use JSONAPI\Exception\HasParameter;
use JSONAPI\Exception\Http\BadRequest;

/**
 * Class ExpressionException
 *
 * @package JSONAPI\URI\Filtering
 */
class ExpressionException extends BadRequest implements HasParameter
{
    public function getParameter(): string
    {
        return 'filter';
    }
}
