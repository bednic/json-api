<?php


namespace JSONAPI\Test\Resources\Invalid;



use JSONAPI\Annotation as API;
use JSONAPI\Data\Collection;

/**
 * Class BadRelationshipGetter
 *
 * @package JSONAPI\Test\Resources\Invalid
 */
#[API\Resource("bad-relationship-getter")]
class BadRelationshipGetter
{

    /**
     * @return Collection
     */
    #[API\Relationship("SomeClass")]
    public function getRelation()
    {
        return  new Collection();
    }
}
