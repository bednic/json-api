<?php

/**
 * Created by IntelliJ IDEA.
 * User: tomas
 * Date: 24.04.2019
 * Time: 12:51
 */

declare(strict_types = 1);

namespace JSONAPI\Test\Resources\Valid;

use JSONAPI\Annotation as API;
use JSONAPI\Metadata\Attribute;
use JSONAPI\Metadata\Id;
use JSONAPI\Schema\Resource;
use JSONAPI\Schema\ResourceSchema;

/**
 * Class DummyRelation
 *
 * @package JSONAPI\Test
 * @API\Resource("relation")
 */
class DummyRelation implements Resource
{
    /**
     * @var string
     * @API\Id
     */
    public string $id;

    /**
     * @var string|null
     * @API\Attribute
     */
    public ?string $property = null;

    public function __construct(string $id)
    {
        $this->id = $id;
    }

    public static function getSchema(): ResourceSchema
    {
        return new ResourceSchema(__CLASS__, 'relation', Id::createByProperty('id'), [
            Attribute::createByProperty('property')
        ]);
    }
}
