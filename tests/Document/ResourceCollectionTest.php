<?php

declare(strict_types=1);

namespace JSONAPI\Test\Document;

use JSONAPI\Document\Id;
use JSONAPI\Document\ResourceCollection;
use JSONAPI\Document\ResourceObject;
use JSONAPI\Document\Type;
use JSONAPI\Exception\Document\ResourceTypeMismatch;
use PHPUnit\Framework\TestCase;

class ResourceCollectionTest extends TestCase
{

//    public function testContains()
//    {
//
//    }
//
//    public function testRemove()
//    {
//
//    }
//
//    public function testGetIterator()
//    {
//
//    }
//
//    public function testGet()
//    {
//
//    }
//
//    public function testAdd()
//    {
//
//    }
//
//    public function testCount()
//    {
//
//    }
//
//    public function testJsonSerialize()
//    {
//
//    }

    public function testConstruct()
    {
        $resource = new ResourceObject(new Type('typed'), new Id('1'));
        $bad = new ResourceObject(new Type('bad'), new Id('2'));
        $collection1 = new ResourceCollection();
        $collection1->add($resource);
        $collection1->add($bad);
        $this->assertInstanceOf(ResourceCollection::class, $collection1);
        $this->expectException(ResourceTypeMismatch::class);
        $collection2 = new ResourceCollection('typed');
        $collection2->add($resource);
        $collection2->add($bad);
    }
}
