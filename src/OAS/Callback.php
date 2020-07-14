<?php

declare(strict_types=1);

namespace JSONAPI\OAS;

/**
 * Class Callback
 *
 * @package JSONAPI\OAS
 */
class Callback extends Reference implements \JsonSerializable
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

    /**
     * @inheritDoc
     */
    public static function createReference(string $to): Callback
    {
        /** @var Callback $static */
        $static = (new \ReflectionClass(__CLASS__))->newInstanceWithoutConstructor();
        $static->setRef($to);
        return $static;
    }
}
