<?php

/**
 * Created by IntelliJ IDEA.
 * User: tomas
 * Date: 24.04.2019
 * Time: 12:51
 */

namespace JSONAPI\Test;

use JSONAPI\Annotation as API;

/**
 * Class DummyRelation
 *
 * @package JSONAPI\Test
 * @API\Resource("relation")
 */
class DummyRelation
{
    /**
     * @var string
     * @API\Id
     */
    public string $id;

    public function __construct(string $id)
    {
        $this->id = $id;
    }
}
