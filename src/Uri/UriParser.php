<?php

namespace JSONAPI\Uri;

use JSONAPI\Exception\InvalidArgumentException;

interface UriParser
{
    /**
     * @param $data
     *
     * @throws InvalidArgumentException
     */
    public function parse($data): void;
}
