<?php

declare(strict_types=1);

namespace JSONAPI\Test\Document;

use JSONAPI\Document\Id;
use JSONAPI\Document\ResourceCollection;
use JSONAPI\Document\ResourceObject;
use JSONAPI\Document\Type;
use PHPUnit\Framework\TestCase;

class ResourceCollectionTest extends TestCase
{
    public function testConstruct()
    {
        $collection = new ResourceCollection();
        $this->assertInstanceOf(ResourceCollection::class, $collection);
        $collection = new ResourceCollection([]);
        $this->assertInstanceOf(ResourceCollection::class, $collection);
        $collection = new ResourceCollection([
            new ResourceObject(new Type('resources'), new Id('id1'))
        ]);
        $this->assertInstanceOf(ResourceCollection::class, $collection);
    }
}
