<?php

/**
 * Created by tomas
 * at 23.01.2021 23:50
 */

declare(strict_types=1);

namespace JSONAPI\Exception\Data;

use JSONAPI\Exception\JsonApiException;

class CollectionException extends JsonApiException
{
    /**
     * @var int
     */
    protected $code = 510;
    /**
     * @var string
     */
    protected $message = "Unknown Collection Exception";
}
