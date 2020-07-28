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
     * @var SecurityScheme|Schema|Response|RequestBody|Parameter|Header|Link|Example|Callback
     */
    protected $origin;
    /**
     * @var string
     */
    protected string $ref;
    /**
     * @var bool
     */
    protected bool $isRef = false;

    /**
     * @param string                                                                            $to
     * @param SecurityScheme|Schema|Response|RequestBody|Parameter|Header|Link|Example|Callback $origin
     *
     * @return static
     */
    abstract public static function createReference(string $to, $origin);

    /**
     * @return bool
     */
    public function isReference()
    {
        return $this->isRef;
    }

    /**
     * @return object
     */
    public function jsonSerialize()
    {
        return (object)['$ref' => $this->ref];
    }

    /**
     * @param string                                                                            $to
     * @param SecurityScheme|Schema|Response|RequestBody|Parameter|Header|Link|Example|Callback $origin
     */
    protected function setRef(string $to, $origin)
    {
        $this->isRef  = true;
        $this->ref    = $to;
        $this->origin = $origin;
    }
}
