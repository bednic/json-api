<?php

declare(strict_types=1);

namespace JSONAPI\Test\Resources\Invalid;

use JSONAPI\Annotation as API;

/**
 * Class BadAnnotationPlacement
 *
 * @package JSONAPI\Test\exceptions
 * @API\Resource("test")
 */
#[API\Resource("test")]
class BadAnnotationPlacement
{

    private $property;

    /**
     * @return mixed
     */
    public function getProperty()
    {
        return $this->property;
    }

    /**
     * @param $property
     */
    #[API\Attribute]
    public function setProperty(
        $property
    ): void {
        $this->property = $property;
    }
}
