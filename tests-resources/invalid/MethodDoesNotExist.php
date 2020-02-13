<?php

declare(strict_types=1);

namespace invalid;

use JSONAPI\Annotation as API;
/**
 * Class MethodDoesNotExist
 *
 * @package invalid
 * @API\Resource(type="method-not-exist", meta=@API\Meta("nonExistingGetter"))
 */
class MethodDoesNotExist
{

}
