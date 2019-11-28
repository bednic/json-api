<?php

namespace JSONAPI\Uri\Filtering;

use JSONAPI\Exception\InvalidArgumentException;
use JSONAPI\Uri\Filter;
use JSONAPI\Uri\UriParser;

/**
 * Class VoidFilterParser
 *
 * @package JSONAPI\Query
 */
class VoidFilterParser implements Filter, UriParser
{
    /**
     * @var string
     */
    private $filter = '';

    /**
     * Function accepts value from filter query param and returns whatever you need
     *
     * @param mixed $data
     *
     * @throws InvalidArgumentException
     */
    public function parse($data): void
    {
        if (!is_string($data)) {
            throw new InvalidArgumentException("Parameter query must be a string.");
        }
        $this->filter = $data;
    }

    /**
     * @return string
     */
    public function getCondition(): string
    {
        return $this->filter;
    }

    public function __toString()
    {
        return 'filter=' . $this->filter;
    }
}
