<?php

namespace JSONAPI\Test\Exception\Driver;

use JSONAPI\Driver\AnnotationDriver;
use JSONAPI\Exception\Driver\BadMethodSignature;
use JSONAPI\Test\Resources\Invalid\WithBadMethodSignature;
use PHPUnit\Framework\TestCase;

/**
 * Class BadMethodSignatureTest
 *
 * @package JSONAPI\Test\Exception\Driver
 */
class BadMethodSignatureTest extends TestCase
{

    protected $runTestInSeparateProcess = true;

    public function testConstruct()
    {
        $e = new BadMethodSignature('someMethod', 'MyClass');
        $this->assertInstanceOf(BadMethodSignature::class, $e);
        $this->assertStringContainsString('someMethod', $e->getMessage());
        $this->assertStringContainsString('MyClass', $e->getMessage());
    }

    public function testUsage()
    {
        $this->expectException(BadMethodSignature::class);
        $driver = new AnnotationDriver();
        $driver->getClassMetadata(WithBadMethodSignature::class);
    }
}
