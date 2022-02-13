<?php

//phpcs:ignoreFile
declare(strict_types=1);

namespace JSONAPI\OAS\Type;

/**
 * Class In
 *
 * @package JSONAPI\OAS
 */
enum In: string
{
    case QUERY = 'query';
    case HEADER = 'header';
    case PATH = 'path';
    case COOKIE = 'cookie';
}
