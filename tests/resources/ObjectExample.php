<?php
/**
 * Created by IntelliJ IDEA.
 * User: tomas
 * Date: 24.04.2019
 * Time: 12:47
 */

namespace JSONAPI\Test;

use \JSONAPI\Annotation as API;

/**
 * Class ObjectExample
 *
 * @package JSONAPI
 * @API\Resource("resource")
 */
class ObjectExample extends Common
{
    protected $id = 'uuid';
    /**
     * @API\Attribute
     * @var string
     */
    public $publicProperty = 'public-value';

    /**
     * @var string
     */
    private $privateProperty = 'private-value';

    /**
     * @var string
     */
    private $readOnlyProperty = 'read-only-value';

    /**
     * @var RelationExample[]
     */
    private $relations = [];

    /**
     * @API\Attribute
     * @return string
     */
    public function getPrivateProperty(): string
    {
        return $this->privateProperty;
    }

    /**
     * @param string $privateProperty
     */
    public function setPrivateProperty(string $privateProperty): void
    {
        $this->privateProperty = $privateProperty;
    }

    /**
     * @API\Attribute(setter="")
     * @return string
     */
    public function getReadOnlyProperty(): string
    {
        return $this->readOnlyProperty;
    }

    /**
     * @API\Relationship(target=RelationExample::class)
     * @return RelationExample[]
     */
    public function getRelations(): array
    {
        return $this->relations;
    }

    /**
     * @param RelationExample[] $relations
     */
    public function setRelations(array $relations): void
    {
        foreach ($relations as $relation) {
            $relation->setObject($this);
        }
        $this->relations = $relations;
    }
}
