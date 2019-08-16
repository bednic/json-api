<?php


namespace JSONAPI\Filter;

use DateTime;
use Exception;
use JSONAPI\Query\Filter;

class ArrayFilterParser implements Filter
{
    const EQUAL = "=";
    const NOT_EQUAL = "!";
    const GREATER_THEN = ">";
    const LOWER_THEN = "<";
    const LIKE = "~";
    const IN = "IN";

    private $filter = [];

    /**
     * Function accepts value from filter query param and returns whatever you need
     *
     * @param $filter
     */
    public function parse($filter)
    {
        $this->filter = [];
        foreach ($filter as $field => $values) {
            if (is_array($values)) {
                foreach ($values as $value) {
                    preg_match('/^(?P<operand>!|>|<|~|)(?P<value>.+)/', $value, $matches);
                    $value = $this->guessDataType($matches["value"]);
                    if (is_array($value)) {
                        $operand = self::IN;
                    } elseif (in_array(
                        $matches["operand"],
                        [
                            self::GREATER_THEN,
                            self::LOWER_THEN,
                            self::NOT_EQUAL,
                            self::LIKE
                        ]
                    )) {
                        $operand = $matches["operand"];
                    } else {
                        $operand = self::EQUAL;
                    }
                    $this->filter[$field][] = new Condition($value, $operand);
                }
            } else {
                preg_match('/^(?P<operand>!|>|<|~|)(?P<value>.+)/', $values, $matches);
                $value = $this->guessDataType($matches["value"]);
                if (is_array($value)) {
                    $operand = self::IN;
                } elseif (in_array(
                    $matches["operand"],
                    [
                        self::GREATER_THEN,
                        self::LOWER_THEN,
                        self::NOT_EQUAL,
                        self::LIKE
                    ]
                )) {
                    $operand = $matches["operand"];
                } else {
                    $operand = self::EQUAL;
                }
                $this->filter[$field] = new Condition($value, $operand);
            }
        }
    }

    /**
     * @param $value
     *
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
        } elseif (strlen($value) >= 4 && strtotime($value)) {
            try {
                return new DateTime($value);
            } catch (Exception $e) {
                return $value;
            }
        } else {
            return (string)$value;
        }
    }

    /**
     * @return mixed
     */
    public function getCondition()
    {
        return $this->filter;
    }
}
