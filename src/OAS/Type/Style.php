<?php

//phpcs:ignoreFile
declare(strict_types=1);

namespace JSONAPI\OAS\Type;

/**
 * Class Style
 *
 * @package JSONAPI\OAS
 */
enum Style: string
{
    case MATRIX = 'matrix';
    case LABEL = 'label';
    case FORM = 'form';
    case SIMPLE = 'simple';
    case SPACE_DELIMITED = 'spaceDelimited';
    case PIPE_DELIMITED = 'pipeDelimited';
    case DEEP_OBJECT = 'deepObject';
}
