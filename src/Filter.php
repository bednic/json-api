<?php
/**
 * Created by IntelliJ IDEA.
 * User: tomas
 * Date: 05.02.2019
 * Time: 13:19
 */

namespace OpenAPI;

class Filter
{


    const LIKE = '~=';
    const GREATER_THEN = '>';
    const LOWER_THEN = '<';
    const EQUAL = '=';
    const IN = '|';

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

    private function parseFields(array $query)
    {
        foreach ($query as $type => $fields) {
            $this->fields[$type] = explode(',', $fields);
        }
    }

    private function parseSort(string $query)
    {
        preg_match_all('/((?P<sort>-?)(?P<field>[a-z]+))/', $query, $matches);
        foreach ($matches['field'] as $i => $field) {
            $this->sort[$field] = $matches['sort'][$i] ? "DESC" : "ASC";
        }
    }

    private function parsePage(array $pagination)
    {
        if (isset($pagination[self::OFFSET])) {
            $this->pagination[self::OFFSET] = (int)$pagination[self::OFFSET];
        }
        if (isset($pagination[self::LIMIT])) {
            $this->pagination[self::LIMIT] = (int)$pagination[self::LIMIT];
        }
    }

    private function parseFilter(array $filters)
    {
        foreach ($filters as $column => $condition) {
            preg_match_all('/(?P<operand>OR|AND|)(?P<comparator>>|<|)(?P<value>[a-z0-9- ]+)/',$condition, $matches);
            foreach ($matches['value'] as $i => $value){
                $this->filter[$column][$matches['operand'][$i]?$matches['operand'][$i]:'OR'][] = [$value, $matches['comparator'][$i]?$matches['comparator'][$i]:'='];
            }
        }
    }

    /**
     * @return array
     */
    public function getIncludes()
    {
        return $this->includes;
    }

    /**
     * @param $resourceType
     * @return array
     */
    public function getFieldsFor($resourceType)
    {
        return isset($this->fields[$resourceType]) ? $this->fields[$resourceType] : [];
    }

    /**
     * @return array
     */
    public function getSort()
    {
        return $this->sort;
    }

    /**
     * @return array
     */
    public function getPagination()
    {
        return $this->pagination;
    }
}
