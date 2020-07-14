<?php

declare(strict_types=1);

namespace JSONAPI\OAS\Enum;

use MyCLabs\Enum\Enum;

/**
 * Class Style
 *
 * @package JSONAPI\OAS
 * @method static Style MATRIX()
 * @method static Style LABEL()
 * @method static Style FORM()
 * @method static Style SIMPLE()
 * @method static Style SPACE_DELIMITED()
 * @method static Style PIPE_DELIMITED()
 * @method static Style DEEP_OBJECT()
 */
class Style extends Enum
{
    public const MATRIX = 'matrix';
    public const LABEL = 'label';
    public const FORM = 'form';
    public const SIMPLE = 'simple';
    public const SPACE_DELIMITED = 'spaceDelimited';
    public const PIPE_DELIMITED = 'pipeDelimited';
    public const DEEP_OBJECT = 'deepObject';
}
