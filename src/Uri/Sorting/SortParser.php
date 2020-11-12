<?php

declare(strict_types=1);

namespace JSONAPI\Uri\Sorting;

use JSONAPI\Exception\Http\MalformedParameter;
use JSONAPI\Uri\QueryPartInterface;

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
     * @param string|null $data
     *
     * @return SortInterface
     * @throws MalformedParameter
     */
    public function parse(?string $data): SortInterface
    {
        $this->sort = [];
        if ($data && strlen($data) > 0) {
            if (preg_match_all('/((?P<sort>-?)(?P<field>[a-zA-Z0-9.]+))/', $data, $matches) !== false) {
                foreach ($matches['field'] as $i => $field) {
                    $this->sort[$field] = $matches['sort'][$i] ? SortInterface::DESC : SortInterface::ASC;
                }
            } else {
                throw new MalformedParameter(QueryPartInterface::SORT_PART_KEY);
            }
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
