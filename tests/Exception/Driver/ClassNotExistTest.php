<?php

declare(strict_types=1);

namespace JSONAPI\Test\Exception\Driver;

use JSONAPI\Driver\AnnotationDriver;
use JSONAPI\Exception\Driver\ClassNotExist;
use PHPUnit\Framework\TestCase;

class ClassNotExistTest extends TestCase
{
    public function testConstruct()
    {
        $e = new ClassNotExist('MyClass');
        $this->assertInstanceOf(ClassNotExist::class, $e);
        $this->assertStringContainsString('MyClass', $e->getMessage());
    }

    public function testUsage()
    {
        $this->expectException(ClassNotExist::class);
        $driver = new AnnotationDriver();
        $driver->getClassMetadata('NonExistingClass');
    }
}
