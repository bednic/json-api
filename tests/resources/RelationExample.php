<?php
/**
 * Created by IntelliJ IDEA.
 * User: tomas
 * Date: 24.04.2019
 * Time: 12:51
 */

namespace Test\JSONAPI;

use JSONAPI\Annotation as API;
/**
 * Class RelationExample
 * @package Test\JSONAPI
 * @API\Resource("resource-relation")
 */
class RelationExample extends Common
{
    protected $id = 'relation-uuid';
    /**
     * @var ObjectExample
     */
    private $object;

    /**
     * @API\Relationship(target=ObjectExample::class)
     * @return ObjectExample
     */
    public function getObject(): ObjectExample
    {
        return $this->object;
    }

    /**
     * @param \Test\JSONAPI\ObjectExample $object
     */
    public function setObject(ObjectExample $object): void
    {
        $this->object = $object;
    }


}
