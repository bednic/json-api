<?php

namespace JSONAPI\Annotation;

use JSONAPI\Driver\AnnotationDriver;
use JSONAPI\Exception\Driver\AnnotationMisplace;
use JSONAPI\Exception\Driver\ClassNotExist;
use JSONAPI\Exception\Driver\ClassNotResource;
use JSONAPI\Exception\Driver\DriverException;
use JSONAPI\Exception\Driver\ReservedWord;
use JSONAPI\Test\BadAnnotationPlacement;
use JSONAPI\Test\GettersExample;
use JSONAPI\Test\NotResource;
use JSONAPI\Test\PropsExample;
use JSONAPI\Test\ReserveWords;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

/**
 * Class AnnotationDriverTest
 *
 * @package JSONAPI\Test
 * @runTestsInSeparateProcesses
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

    public function testBadAnnotationPlacementException()
    {
        $this->expectException(AnnotationMisplace::class);
        $driver = new AnnotationDriver();
        $driver->getClassMetadata(get_class(new BadAnnotationPlacement()));
    }

    public function testClassNotExistsException()
    {
        $this->expectException(ClassNotExist::class);
        $driver = new AnnotationDriver();
        $driver->getClassMetadata('NonExistingClass');
    }

    public function testClassNotResource()
    {
        $this->expectException(ClassNotResource::class);
        $driver = new AnnotationDriver();
        $driver->getClassMetadata(get_class(new NotResource()));
    }

    public function testCheckReservedNamesException()
    {
        $this->expectException(ReservedWord::class);
        $driver = new AnnotationDriver();
        $driver->getClassMetadata(get_class(new ReserveWords()));
    }

    /**
     * @dataProvider classProvider
     */
    public function testGetClassMetadata($instace)
    {
        $driver = new AnnotationDriver();
        $metadata = $driver->getClassMetadata(get_class($instace));
        $this->assertNotEmpty($metadata->getClassName());
        $this->assertInstanceOf(Id::class, $metadata->getId());
        $this->assertInstanceOf(Resource::class, $metadata->getResource());
        $this->assertInstanceOf(Attribute::class, $metadata->getAttribute('stringProperty'));
        $this->assertInstanceOf(Attribute::class, $metadata->getAttribute('intProperty'));
        $this->assertInstanceOf(Attribute::class, $metadata->getAttribute('arrayProperty'));
        $this->assertInstanceOf(Attribute::class, $metadata->getAttribute('boolProperty'));
        $this->assertInstanceOf(Attribute::class, $metadata->getAttribute('dtoProperty'));
        $this->assertInstanceOf(Relationship::class, $metadata->getRelationship('relation'));
        $this->assertInstanceOf(Relationship::class, $metadata->getRelationship('collection'));
    }

    public function classProvider(){
        return [
            [new PropsExample('test')],
            [new GettersExample('test')]
        ];
    }
}
