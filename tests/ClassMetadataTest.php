<?php

/**
 * Created by IntelliJ IDEA.
 * User: tomas
 * Date: 24.04.2019
 * Time: 13:35
 */

namespace JSONAPI\Test;

use JSONAPI\Annotation\Attribute;
use JSONAPI\Annotation\Id;
use JSONAPI\Annotation\Relationship;
use JSONAPI\Annotation\Resource;
use JSONAPI\Metadata\ClassMetadata;
use JSONAPI\Metadata\MetadataFactory;
use PHPUnit\Framework\TestCase;

/**
 * Class ClassMetadataTest
 *
 * @package JSONAPI\Test
 */
class ClassMetadataTest extends TestCase
{

    /**
     * @var ClassMetadata
     */
    private static $metadata;

    public static function setUpBeforeClass(): void
    {
        $factory = new MetadataFactory(__DIR__ . '/resources/');
        self::$metadata = $factory->getMetadataByClass(ObjectExample::class);
    }

    public function testGetId()
    {
        $id = self::$metadata->getId();
        $this->assertInstanceOf(Id::class, $id);
        $this->assertNull($id->property);
        $this->assertEquals('getId', $id->getter);
    }

    public function testGetResource()
    {
        $resource = self::$metadata->getResource();
        $this->assertInstanceOf(Resource::class, $resource);
        $this->assertEquals('resource', $resource->type);
        $this->assertEquals(true, $resource->public);
    }

    public function testGetAttributes()
    {
        $attributes = self::$metadata->getAttributes()->toArray();
        $this->assertIsArray($attributes);
        $this->assertContainsOnlyInstancesOf(Attribute::class, $attributes);
    }

    public function testGetRelationship()
    {
        $relationship = self::$metadata->getRelationship('relations');
        $this->assertInstanceOf(Relationship::class, $relationship);
        $this->assertTrue($relationship->isCollection);

        $nullable = self::$metadata->getRelationship('nonExisting');
        $this->assertNull($nullable);
    }

    public function testGetRelationships()
    {
        $relationships = self::$metadata->getRelationships()->toArray();
        $this->assertIsArray($relationships);
        $this->assertContainsOnlyInstancesOf(Relationship::class, $relationships);
    }

    public function testIsRelationship()
    {
        $this->assertTrue(self::$metadata->isRelationship('relations'));
        $this->assertFalse(self::$metadata->isRelationship('privateProperty'));
    }

    public function testIsAttribute()
    {
        $this->assertTrue(self::$metadata->isAttribute('privateProperty'));
        $this->assertFalse(self::$metadata->isAttribute('relations'));
    }

    public function testReadOnlyProperty()
    {
        $attribute = self::$metadata->getAttribute('readOnlyProperty');
        $this->assertInstanceOf(Attribute::class, $attribute);
        $this->assertEquals("", $attribute->setter, "Readonly property should have ::setter to ''");
    }

    public function testPrivateProperty()
    {
        $attribute = self::$metadata->getAttribute('privateProperty');
        $this->assertInstanceOf(Attribute::class, $attribute);
        $this->assertNull($attribute->property);
        $this->assertEquals('privateProperty', $attribute->name);
        $this->assertEquals('getPrivateProperty', $attribute->getter);
        $this->assertEquals('setPrivateProperty', $attribute->setter);
    }

    public function testPublicProperty()
    {
        $attribute = self::$metadata->getAttribute('publicProperty');
        $this->assertInstanceOf(Attribute::class, $attribute);
        $this->assertEquals('publicProperty', $attribute->name);
        $this->assertNull($attribute->getter);
        $this->assertNull($attribute->setter);
        $this->assertNotEmpty($attribute->property);
        $this->assertEquals('publicProperty', $attribute->property);
    }
}
