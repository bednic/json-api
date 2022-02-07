<?php

declare(strict_types=1);

namespace JSONAPI\OAS\Type;

use MyCLabs\Enum\Enum;

/**
 * Class In
 *
 * @package JSONAPI\OAS
 * @method static In QUERY()
 * @method static In HEADER()
 * @method static In PATH()
 * @method static In COOKIE()
 */
class In extends Enum
{
    public const QUERY  = 'query';
    public const HEADER = 'header';
    public const PATH   = 'path';
    public const COOKIE = 'cookie';
}
