<?php

declare(strict_types=1);

namespace JSONAPI\OAS;

use JSONAPI\Document\Serializable;
use ReflectionClass;

/**
 * Class Callback
 *
 * @package JSONAPI\OAS
 */
class Callback extends Reference implements Serializable
{
    /**
     * @var string
     */
    private string $expression;
    /**
     * @var PathItem
     */
    private PathItem $pathItem;

    /**
     * Callback constructor.
     *
     * @param string   $expression
     * @param PathItem $pathItem
     */
    public function __construct(string $expression, PathItem $pathItem)
    {
        $this->expression = $expression;
        $this->pathItem   = $pathItem;
    }

    /**
     * @inheritDoc
     */
    public static function createReference(string $to, $origin): Callback
    {
        /** @var Callback $static */
        $static = (new ReflectionClass(__CLASS__))->newInstanceWithoutConstructor(); // NOSONAR
        $static->setRef($to, $origin);
        return $static;
    }

    public function jsonSerialize()
    {
        if ($this->isReference()) {
            return parent::jsonSerialize();
        }
        $ret = [
            $this->expression => $this->pathItem
        ];
        return (object)$ret;
    }
}
