<?php

declare(strict_types=1);

namespace JSONAPI\Uri\Fieldset;

use JSONAPI\Uri\Sorting\SortInterface;

/**
 * Class SortParser
 *
 * @package JSONAPI\Uri\Fieldset
 */
class SortParser implements SortInterface
{
    /**
     * @var array
     */
    private array $sort = [];

    /**
     * @param string $data
     *
     * @return SortInterface
     */
    public function parse(string $data): SortInterface
    {
        //@todo: this should be able to parse field and relation.field
        $this->sort = [];
        preg_match_all('/((?P<sort>-?)(?P<field>[a-zA-Z0-9.]+))/', $data, $matches);
        foreach ($matches['field'] as $i => $field) {
            $this->sort[$field] = $matches['sort'][$i] ? SortInterface::DESC : SortInterface::ASC;
        }
        return $this;
    }

    /**
     * @return array associative array contains field as key and order as value
     * @example [
     *      "fieldA" => "DESC",
     *      "fieldB" => "ASC"
     * ]
     */
    public function getOrder(): array
    {
        return $this->sort;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        $str = '';
        if (count($this->sort) > 0) {
            foreach ($this->sort as $field => $sort) {
                $str .= (strlen($str) > 0 ? ',' : '') . ($sort === 'DESC' ? '-' : '') . $field;
            }
        }
        return $str;
    }
}
