<?php

declare(strict_types=1);

namespace JSONAPI\Test\Document;

use ArrayIterator;
use JSONAPI\Annotation\Resource;
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
        $collection = new ResourceCollection(
            [
                new ResourceObject(new Type('resources'), new Id('id1'))
            ]
        );
        $this->assertInstanceOf(ResourceCollection::class, $collection);
    }

    public function testFind()
    {
        $collection = new ResourceCollection(
            [
                new ResourceObject(new Type('type'), new Id('1')),
                new ResourceObject(new Type('type'), new Id('2'))
            ]
        );
        $this->assertInstanceOf(ResourceObject::class, $collection->find('type', '1'));
        $this->assertEquals('1', $collection->find('type', '1')->getId());
        $this->assertInstanceOf(ResourceObject::class, $collection->find('type', '2'));
        $this->assertEquals('2', $collection->find('type', '2')->getId());
        $this->assertNull($collection->find('type', '3'));
    }

    public function testReset()
    {
        $collection = new ResourceCollection(
            [
                new ResourceObject(new Type('type'), new Id('1')),
                new ResourceObject(new Type('type'), new Id('2'))
            ]
        );
        $this->assertEquals(2, $collection->count());
        $collection->reset();
        $this->assertEquals(0, $collection->count());
    }

    public function testRemove()
    {
        $resource = new ResourceObject(new Type('type'), new Id('1'));
        $collection = new ResourceCollection();
        $collection->add($resource);
        $this->assertEquals($resource, $collection->find('type', '1'));
        $this->assertTrue($collection->remove($resource));
        $this->assertNull($collection->find('type', '1'));
        $this->assertFalse($collection->remove($resource));
    }

    public function testContains()
    {
        $resource = new ResourceObject(new Type('type'), new Id('1'));
        $nonexist = new ResourceObject(new Type('type'), new Id('2'));
        $collection = new ResourceCollection();
        $collection->add($resource);
        $this->assertTrue($collection->contains($resource));
        $this->assertFalse($collection->contains($nonexist));
    }

    public function testGetIterator()
    {
        $collection = new ResourceCollection();
        $this->assertInstanceOf(ArrayIterator::class, $collection->getIterator());
    }

    public function testToArray()
    {
        $resource = new ResourceObject(new Type('type'), new Id('1'));
        $collection = new ResourceCollection();
        $collection->add($resource);
        $this->assertIsArray($collection->toArray());
    }
}
