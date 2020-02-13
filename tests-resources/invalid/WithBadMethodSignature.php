<?php

declare(strict_types=1);

namespace JSONAPI\Test\Resources\Invalid;

use JSONAPI\Annotation as API;

/**
 * Class WithBadMethodSignature
 *
 * @package JSONAPI\Test\Resources\Invalid
 * @API\Resource("bad-signature")
 */
class WithBadMethodSignature
{
    private $property;

    /**
     * @API\Attribute
     * @return mixed
     */
    public function getProperty()
    {
        return $this->property;
    }

    /**
     * @param mixed $property
     * @param       $anotherArgument
     */
    public function setProperty($property, $anotherArgument): void
    {
        $this->property = $property;
    }
}
