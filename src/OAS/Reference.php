<?php

declare(strict_types=1);

namespace JSONAPI\OAS;

/**
 * Class Reference
 *
 * @package JSONAPI\OAS
 */
abstract class Reference
{
    /**
     * @var string
     */
    protected string $ref;
    /**
     * @var bool
     */
    protected bool $isRef = false;

    /**
     * @return bool
     */
    protected function isReference()
    {
        return $this->isRef;
    }

    /**
     * @param string $to
     */
    protected function setRef(string $to)
    {
        $this->isRef = true;
        $this->ref   = $to;
    }

    /**
     * @param string $to
     *
     * @return static
     */
    abstract public static function createReference(string $to);

    /**
     * @return object
     */
    public function jsonSerialize()
    {
        return (object)['$ref' => $this->ref];
    }
}
