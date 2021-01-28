<?php

declare(strict_types=1);

namespace JSONAPI\URI\Sorting;

use JSONAPI\Exception\Http\MalformedParameter;
use JSONAPI\URI\QueryPartInterface;

/**
 * Class SortParser
 *
 * @package JSONAPI\URI\Fieldset
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
        if (!is_null($data) && strlen($data) > 0) {
            $parts = explode(',', $data);
            $parts = array_map('trim', $parts);
            foreach ($parts as $part) {
                if (
                    preg_match(
                        '/^(?P<sort>(-|))(?P<field>([a-zA-Z0-9]([a-zA-Z0-9-_.]*[a-zA-Z0-9])?))$/',
                        $part,
                        $matches
                    )
                ) {
                    $this->sort[$matches['field']] = strlen($matches['sort']) ? SortInterface::DESC
                        : SortInterface::ASC;
                } else {
                    throw new MalformedParameter(QueryPartInterface::SORT_PART_KEY);
                }
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
