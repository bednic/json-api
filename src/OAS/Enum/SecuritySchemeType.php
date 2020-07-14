<?php

declare(strict_types=1);

namespace JSONAPI\OAS\Enum;

use MyCLabs\Enum\Enum;

/**
 * Class SecuritySchemeType
 *
 * @package JSONAPI\OAS
 * @method static SecuritySchemeType API_KEY()
 * @method static SecuritySchemeType HTTP()
 * @method static SecuritySchemeType OAUTH2()
 * @method static SecuritySchemeType OPEN_ID_CONNECT()
 */
class SecuritySchemeType extends Enum
{
    public const API_KEY = 'apiKey';
    public const HTTP = 'http';
    public const OAUTH2 = 'oauth2';
    public const OPEN_ID_CONNECT = 'openIdConnect';
}
