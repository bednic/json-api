<?php

declare(strict_types=1);

namespace JSONAPI\Test\Resources\Valid;

use JSONAPI\Metadata\Attribute;
use JSONAPI\Metadata\Id;
use JSONAPI\Schema\Resource;
use JSONAPI\Schema\ResourceSchema;
use JSONAPI\Annotation as API;

/**
 * Class ThirdLevel
 *
 * @package JSONAPI\Test\Resources\Valid
 */
#[API\Resource("third")]
class ThirdLevel implements Resource
{
    /**
     * @var string
     */
    #[API\Id]
    public string $id;

    /**
     * @var string|null
     */
    #[API\Attribute]
    public ?string $property = null;

    public function __construct(string $id)
    {
        $this->id = $id;
    }

    public static function getSchema(): ResourceSchema
    {
        return new ResourceSchema(
            __CLASS__,
            'third',
            Id::createByProperty('id'),
            [Attribute::createByProperty('property')],
        );
    }
}
