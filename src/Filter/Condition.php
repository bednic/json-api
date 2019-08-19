<?php
/**
 * Created by IntelliJ IDEA.
 * User: tomas
 * Date: 07.03.2019
 * Time: 16:01
 */

namespace JSONAPI\Filter;

/**
 * Class Condition
 *
 * @package JSONAPI\Query
 * @deprecated
 */
class Condition
{

    public $operand = ArrayFilterParser::EQUAL;
    public $value;

    /**
     * Condition constructor.
     *
     * @param        $value
     * @param string $operand
     */
    public function __construct($value, $operand = ArrayFilterParser::EQUAL)
    {
        $this->value = $value;
        $this->operand = $operand;
    }
}
