<?php


namespace JSONAPI\Test\Resources\Invalid;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use JSONAPI\Annotation as API;

/**
 * Class BadRelationshipGetter
 *
 * @package JSONAPI\Test\Resources\Invalid
 * @API\Resource("bad-relationship-getter")
 */
class BadRelationshipGetter
{

    /**
     * @API\Relationship(target="SomeClass")
     * @return Collection
     */
    public function getRelation()
    {
        return  new ArrayCollection();
    }
}
