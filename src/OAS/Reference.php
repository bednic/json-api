<?php

declare(strict_types=1);

namespace JSONAPI\OAS;

use JSONAPI\Exception\OAS\OpenAPIException;

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
    protected mixed $origin;
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
     * @throws OpenAPIException
     */
    abstract public static function createReference(
        string $to,
        mixed $origin
    ): mixed;

    /**
     * @return bool
     */
    public function isReference(): bool
    {
        return $this->isRef;
    }

    /**
     * @return object
     */
    public function jsonSerialize(): object
    {
        return (object)['$ref' => $this->ref];
    }

    /**
     * @param string                                                                            $to
     * @param SecurityScheme|Schema|Response|RequestBody|Parameter|Header|Link|Example|Callback $origin
     */
    protected function setRef(string $to, mixed $origin): void
    {
        $this->isRef = true;
        $this->ref = $to;
        $this->origin = $origin;
    }
}
