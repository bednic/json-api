<?php
/**
 * Created by IntelliJ IDEA.
 * User: tomas
 * Date: 05.02.2019
 * Time: 13:19
 */

namespace JSONAPI;

/**
 * Class Filter
 * @package JSONAPI
 */
class Filter
{

    const EQUAL = "=";
    const NOT_EQUAL = "!";
    const GREATER_THEN = ">";
    const LOWER_THEN = "<";
    const LIKE = "~";
    const IN = "IN";

    const OFFSET = 'offset';
    const LIMIT = 'limit';

    private $includes = [];
    private $fields = [];
    private $sort = [];
    private $filter = [];
    private $pagination = [
        self::OFFSET => 0,
        self::LIMIT => 25
    ];

    public function __construct()
    {
        if (isset($_GET['include'])) {
            $this->parseIncludes($_GET['include']);
        }

        if (isset($_GET['fields'])) {
            $this->parseFields($_GET['fields']);
        }
        if (isset($_GET['sort'])) {
            $this->parseSort($_GET['sort']);
        }
        if (isset($_GET['page'])) {
            $this->parsePage($_GET['page']);
        }

        if (isset($_GET['filter'])) {
            $this->parseFilter($_GET['filter']);
        }


    }

    /**
     * @return array
     */
    public function getIncludes(): array
    {
        return $this->includes;
    }

    /**
     * @param $resourceType
     * @return array
     */
    public function getFieldsFor($resourceType): array
    {
        return isset($this->fields[$resourceType]) ? $this->fields[$resourceType] : [];
    }

    /**
     * @return array
     */
    public function getSort(): array
    {
        return $this->sort;
    }

    /**
     * @return array
     */
    public function getPagination(): array
    {
        return $this->pagination;
    }

    /**
     * @return array
     */
    public function getFilter(): array
    {
        return $this->filter;
    }

    /**
     * @param string $query
     */
    private function parseIncludes(string $query)
    {
        $t = explode(",", $query);
        $dot2tree = function (&$arr, $path, $value, $separator = '.') {
            $keys = explode($separator, $path);
            foreach ($keys as $key) {
                $arr = &$arr[$key];
            }

            $arr = $value;
        };
        foreach ($t as $i) {
            $dot2tree($this->includes, $i, []);
        }
    }

    /**
     * @param array $query
     */
    private function parseFields(array $query)
    {
        foreach ($query as $type => $fields) {
            $this->fields[$type] = explode(',', $fields);
        }
    }

    /**
     * @param string $query
     */
    private function parseSort(string $query)
    {
        preg_match_all('/((?P<sort>-?)(?P<field>[a-z]+))/', $query, $matches);
        foreach ($matches['field'] as $i => $field) {
            $this->sort[$field] = $matches['sort'][$i] ? "DESC" : "ASC";
        }
    }

    /**
     * @param array $pagination
     */
    private function parsePage(array $pagination)
    {
        if (isset($pagination[self::OFFSET])) {
            $this->pagination[self::OFFSET] = (int)$pagination[self::OFFSET];
        }
        if (isset($pagination[self::LIMIT])) {
            $this->pagination[self::LIMIT] = (int)$pagination[self::LIMIT];
        }
    }

    /**
     * @param array $filters
     */
    private function parseFilter(array $filters)
    {
        foreach ($filters as $field => $value) {
            preg_match('/^(?P<operand>!|>|<|)(?P<value>.+)/', $value, $matches);
            $value = $this->guessDataType($matches["value"]);
            if (is_array($value)) {
                $operand = self::IN;
            } elseif (in_array($matches["operand"], [self::GREATER_THEN, self::LOWER_THEN, self::NOT_EQUAL, self::LIKE])) {
                $operand = $matches["operand"];
            } else {
                $operand = self::EQUAL;
            }
            $this->filter[$field] = [$operand, $value];
        }
    }

    /**
     * @param $value
     * @return mixed
     */
    private function guessDataType($value)
    {
        if (preg_match('/^\[([a-zA-Z0-9-_. ]+\,?)+\]$/', $value, $matches)) {
            preg_match_all('/([a-zA-Z0-9-_. ]+)\,?/', $matches[0], $values);
            $ret = $values[1];
            foreach ($ret as &$item) {
                $item = $this->guessDataType($item);
            }
            return $ret;
        } elseif ($ret = filter_var($value, FILTER_VALIDATE_INT)) {
            return $ret;
        } elseif ($ret = filter_var($value, FILTER_VALIDATE_FLOAT)) {
            return $ret;
        } elseif (($ret = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)) !== null) {
            return $ret;
        } elseif ($value === "null") {
            return null;
        } elseif (strtotime($value)) {
            try {
                return new \DateTime($value);
            } catch (\Exception $e) {
                return $value;
            }
        } else {
            return (string)$value;
        }
    }
}
