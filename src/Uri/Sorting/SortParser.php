<?php

namespace JSONAPI\Uri\Fieldset;

use JSONAPI\Exception\InvalidArgumentException;
use JSONAPI\Uri\UriParser;

/**
 * Class SortParser
 *
 * @package JSONAPI\Uri\Fieldset
 */
class SortParser implements UriParser
{
    /**
     * @var array
     */
    private $sort = [];

    /**
     * @param $data
     *
     * @throws InvalidArgumentException
     */
    public function parse($data): void
    {
        if (!is_string($data)) {
            throw new InvalidArgumentException('Parameter $query must be string.');
        }
        $this->sort = [];
        preg_match_all('/((?P<sort>-?)(?P<field>[a-zA-Z0-9]+))/', $data, $matches);
        foreach ($matches['field'] as $i => $field) {
            $this->sort[$field] = $matches['sort'][$i] ? 'DESC' : 'ASC';
        }
    }

    /**
     * @return array
     */
    public function getFieldsSort(): array
    {
        return $this->sort;
    }

    /**
     * @param string $field
     *
     * @return string|null
     */
    public function getSortForField(string $field): ?string
    {
        return isset($this->sort[$field]) ? $this->sort[$field] : null;
    }

    public function __toString()
    {
        $str = '';
        foreach ($this->sort as $field => $sort) {
            $str .= (strlen($str) > 0 ? ',' : '') . ($sort === 'DESC' ? '-' : '') . $field;
        }
        return 'sort=' . $str;
    }
}
