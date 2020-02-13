<?php

declare(strict_types=1);

namespace JSONAPI\Test\Exception\Driver;

use JSONAPI\Driver\AnnotationDriver;
use JSONAPI\Exception\Driver\ClassNotResource;
use JSONAPI\Test\Resources\Invalid\NotResource;
use PHPUnit\Framework\TestCase;

class ClassNotResourceTest extends TestCase
{
    protected $runTestInSeparateProcess = true;

    public function testConstruct()
    {
        $e = new ClassNotResource('MyClass');
        $this->assertInstanceOf(ClassNotResource::class, $e);
        $this->assertStringContainsString('MyClass', $e->getMessage());
    }
    public function testUsage()
    {
        $this->expectException(ClassNotResource::class);
        $driver = new AnnotationDriver();
        $driver->getClassMetadata(get_class(new NotResource()));
    }
}
