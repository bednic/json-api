<?php

namespace JSONAPI\Test\Driver;

use JSONAPI\Driver\AnnotationDriver;
use JSONAPI\Metadata\Attribute;
use JSONAPI\Metadata\Id;
use JSONAPI\Metadata\Meta;
use JSONAPI\Metadata\Relationship;
use JSONAPI\Test\Resources\Valid\DtoValue;
use JSONAPI\Test\Resources\Valid\GettersExample;
use JSONAPI\Test\Resources\Valid\MetaExample;
use JSONAPI\Test\Resources\Valid\PropsExample;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

/**
 * Class AnnotationDriverTest
 *
 * @package JSONAPI\Test
 */
class AnnotationDriverTest extends TestCase
{

    public function testConstruct()
    {
        $driver = new AnnotationDriver();
        $this->assertInstanceOf(AnnotationDriver::class, $driver);
        $driver = new AnnotationDriver(new NullLogger());
        $this->assertInstanceOf(AnnotationDriver::class, $driver);
        return $driver;
    }

    public function testMetaAnnotation()
    {
        $driver = new AnnotationDriver();
        $resource = new MetaExample('test');
        $metadata = $driver->getClassMetadata(get_class($resource));
        $this->assertInstanceOf(Meta::class, $metadata->getMeta());
        $this->assertEquals('getMeta', $metadata->getMeta()->getter);
        $this->assertInstanceOf(Meta::class, $metadata->getRelationship('relation')->meta);
        $this->assertEquals('getRelationMeta', $metadata->getRelationship('relation')->meta->getter);
    }

    /**
     * @dataProvider classProvider
     */
    public function testGetClassMetadata($instance)
    {
        $driver = new AnnotationDriver();
        $metadata = $driver->getClassMetadata(get_class($instance));
        $this->assertNotEmpty($metadata->getClassName());
        $this->assertInstanceOf(Id::class, $metadata->getId());
        $this->assertIsString($metadata->getType());
        $this->assertInstanceOf(Attribute::class, $metadata->getAttribute('stringProperty'));
        $this->assertEquals('string', $metadata->getAttribute('stringProperty')->type);
        $this->assertInstanceOf(Attribute::class, $metadata->getAttribute('intProperty'));
        $this->assertEquals('int', $metadata->getAttribute('intProperty')->type);
        $this->assertInstanceOf(Attribute::class, $metadata->getAttribute('arrayProperty'));
        $this->assertEquals('array', $metadata->getAttribute('arrayProperty')->type);
        $this->assertEquals('int', $metadata->getAttribute('arrayProperty')->of);
        $this->assertInstanceOf(Attribute::class, $metadata->getAttribute('boolProperty'));
        $this->assertEquals('bool', $metadata->getAttribute('boolProperty')->type);
        $this->assertInstanceOf(Attribute::class, $metadata->getAttribute('dtoProperty'));
        $this->assertEquals(DtoValue::class, $metadata->getAttribute('dtoProperty')->type);
        $this->assertInstanceOf(Relationship::class, $metadata->getRelationship('relation'));
        $this->assertInstanceOf(Relationship::class, $metadata->getRelationship('collection'));
    }

    public function classProvider()
    {
        return [
            [new PropsExample('test')],
            [new GettersExample('test')]
        ];
    }
}
