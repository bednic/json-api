<?php

namespace JSONAPI\Uri\Fieldset;

use JSONAPI\Exception\InvalidArgumentException;
use JSONAPI\Uri\SparseFieldset;
use JSONAPI\Uri\UriParser;

class FieldsetParser implements UriParser, SparseFieldset
{
    /**
     * @var array
     */
    private $fields = [];

    /**
     * @param $data
     *
     * @throws InvalidArgumentException
     */
    public function parse($data): void
    {
        if (!is_array($data)) {
            throw new InvalidArgumentException('Parameter $query must be an array.');
        }

        $this->fields = [];
        foreach ($data as $type => $fields) {
            $this->fields[$type] = array_map(function ($item) {
                return trim($item);
            }, explode(',', $fields));
        }
    }

    public function showField(string $type, string $fieldName): bool
    {
        if (!isset($this->fields[$type])) {
            return true;
        } elseif (isset($this->fields[$type][$fieldName])) {
            return true;
        } else {
            return false;
        }
    }

    public function __toString()
    {
        $str = '';
        foreach ($this->fields as $type => $fields) {
            $str .= (strlen($str) > 0 ? '&' : '') . "fields[$type]=" . implode(',', $fields);
        }
        return $str;
    }
}
