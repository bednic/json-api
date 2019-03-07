<?php
/**
 * Created by IntelliJ IDEA.
 * User: tomas
 * Date: 07.03.2019
 * Time: 16:01
 */

namespace JSONAPI\Filter;

class Condition
{

    public $operand = Filter::EQUAL;
    public $value;

    public function __construct($value, $operand = Filter::EQUAL)
    {
        $this->value = $value;
        $this->operand = $operand;
    }

}
