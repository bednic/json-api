<?php

namespace JSONAPI\Test;

use JSONAPI\Driver\AnnotationDriver;
use JSONAPI\Exception\Driver\AnnotationMisplace;
use JSONAPI\Exception\Driver\ClassNotExist;
use JSONAPI\Exception\Driver\ClassNotResource;
use PHPUnit\Framework\TestCase;

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
        return $driver;
    }

    /**
     * @depends testConstruct
     */
    public function testGetClassMetadata(AnnotationDriver $driver)
    {
        $this->expectException(AnnotationMisplace::class);
        $annotationMisplace = new BadAnnotationPlacement();
        $driver->getClassMetadata(get_class($annotationMisplace));
    }

    /**
     * @depends testConstruct
     */
    public function testClassNotExists(AnnotationDriver $driver)
    {
        $this->expectException(ClassNotExist::class);
        $driver->getClassMetadata('NonExistingClass');
    }

    /**
     * @depends testConstruct
     */
    public function testClassNotResource(AnnotationDriver $driver)
    {
        $this->expectException(ClassNotResource::class);
        $notResource = new NotResource();
        $driver->getClassMetadata(get_class($notResource));
    }
}
