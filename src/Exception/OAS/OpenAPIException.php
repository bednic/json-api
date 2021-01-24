<?php

declare(strict_types=1);

namespace JSONAPI\Exception\OAS;

use Exception;

/**
 * Class OpenAPIException
 *
 * @package JSONAPI\Exception\OAS
 */
abstract class OpenAPIException extends Exception
{
    protected $code = 560;

}
