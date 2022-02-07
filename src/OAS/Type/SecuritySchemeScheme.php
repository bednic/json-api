<?php

declare(strict_types=1);

namespace JSONAPI\OAS\Type;

use MyCLabs\Enum\Enum;

/**
 * Class SecuritySchemeScheme
 *
 * @package JSONAPI\OAS
 * @method static SecuritySchemeScheme BASIC()
 * @method static SecuritySchemeScheme BEARER()
 * @method static SecuritySchemeScheme DIGEST()
 * @method static SecuritySchemeScheme HOBA()
 * @method static SecuritySchemeScheme MUTUAL()
 * @method static SecuritySchemeScheme NEGOTIATE()
 * @method static SecuritySchemeScheme OAUTH()
 * @method static SecuritySchemeScheme SCRAM_SHA_1()
 * @method static SecuritySchemeScheme SCRAM_SHA_256()
 * @method static SecuritySchemeScheme VAPID()
 */
class SecuritySchemeScheme extends Enum
{
    public const BASIC         = 'Basic';
    public const BEARER        = 'Bearer';
    public const DIGEST        = 'Digest';
    public const HOBA          = 'HOBA';
    public const MUTUAL        = 'Mutual';
    public const NEGOTIATE     = 'Negotiate';
    public const OAUTH         = 'OAuth';
    public const SCRAM_SHA_1   = 'SCRAM-SHA-1';
    public const SCRAM_SHA_256 = 'SCRAM-SHA-256';
    public const VAPID         = 'vapid';
}
