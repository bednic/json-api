<?php

namespace JSONAPI\Test\Resources\Invalid;

use JSONAPI\Metadata as API;

/**
 * Class ReserveWords
 *
 * @package JSONAPI\Test
 * @API\Resource("test")
 */
class ReserveWords
{
    /**
     * @var string
     * @API\Attribute
     */
    public string $type;

    /**
     * @var int
     * @API\Id
     */
    public int $id;

    /**
     * @API\Attribute
     * @return string
     */
    public function getId(): string
    {
        return 'test';
    }
}
