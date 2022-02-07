<?php

declare(strict_types=1);

namespace JSONAPI\Test\Exception\Driver;

use JSONAPI\Driver\AnnotationDriver;
use JSONAPI\Exception\Driver\BadSignature;
use JSONAPI\Test\Resources\Invalid\BadRelationshipGetter;
use JSONAPI\Test\Resources\Invalid\WithBadMethodSignature;
use PHPUnit\Framework\TestCase;

/**
 * Class BadMethodSignatureTest
 *
 * @package JSONAPI\Test\Exception\Driver
 */
class BadMethodSignatureTest extends TestCase
{
    public function testConstruct()
    {
        $e = new BadSignature('someMethod', 'MyClass');
        $this->assertInstanceOf(BadSignature::class, $e);
        $this->assertStringContainsString('someMethod', $e->getMessage());
        $this->assertStringContainsString('MyClass', $e->getMessage());
    }

    public function testUsage()
    {
        $this->expectException(BadSignature::class);
        $driver = new AnnotationDriver();
        $driver->getClassMetadata(WithBadMethodSignature::class);
    }

    public function testBadRelationshipGetter()
    {
        $this->expectException(BadSignature::class);
        $driver = new AnnotationDriver();
        $driver->getClassMetadata(BadRelationshipGetter::class);
    }
}
