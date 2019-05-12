<?php
/**
 * Created by IntelliJ IDEA.
 * User: tomas
 * Date: 07.03.2019
 * Time: 16:01
 */

namespace JSONAPI\Query;

/**
 * Class Condition
 *
 * @package JSONAPI\Query
 */
class Condition
{

    public $operand = Query::EQUAL;
    public $value;

    /**
     * Condition constructor.
     *
     * @param        $value
     * @param string $operand
     */
    public function __construct($value, $operand = Query::EQUAL)
    {
        $this->value = $value;
        $this->operand = $operand;
    }
}
