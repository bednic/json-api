<?php


namespace JSONAPI\Test;

/**
 * Class BadAnnotationPlacement
 *
 * @package JSONAPI\Test\exceptions
 * @API\Resource("test")
 */
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
     * @param mixed $property
     *
     * @API\Attribute
     */
    public function setProperty($property): void
    {
        $this->property = $property;
    }
}
