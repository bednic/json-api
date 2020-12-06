<?php

declare(strict_types=1);

namespace invalid;

use JSONAPI\Annotation as API;

/**
 * Class MethodDoesNotExist
 *
 * @package invalid
 */
#[API\Resource("method-not-exist")]
#[API\Meta("nonExistingGetter")]
class MethodDoesNotExist
{

}
