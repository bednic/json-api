<?php


namespace JSONAPI\Test;

use JSONAPI\Annotation as API;

/**
 * Class ReserveWords
 *
 * @package JSONAPI\Test
 * @API\Resource(type="test")
 */
class ReserveWords
{
    /**
     * @var string
     * @API\Attribute
     */
    public string $type;

    /**
     * @API\Attribute
     * @return string
     */
    public function getId(): string
    {
        return 'test';
    }
}
