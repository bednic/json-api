<?php

declare(strict_types=1);

namespace JSONAPI\Test\Exception\Driver;

use invalid\MethodDoesNotExist;
use JSONAPI\Driver\AnnotationDriver;
use JSONAPI\Exception\Driver\MethodNotExist;
use PHPUnit\Framework\TestCase;

class MethodNotExistTest extends TestCase
{


    public function testConstruct()
    {
        $e = new MethodNotExist('someMethod', 'MyClass');
        $this->assertInstanceOf(MethodNotExist::class, $e);
        $this->assertStringContainsString('someMethod', $e->getMessage());
        $this->assertStringContainsString('MyClass', $e->getMessage());
    }

    public function testUsage()
    {
        $this->expectException(MethodNotExist::class);
        $driver = new AnnotationDriver();
        $driver->getClassMetadata(MethodDoesNotExist::class);
    }
}
