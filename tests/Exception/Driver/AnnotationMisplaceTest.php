<?php

declare(strict_types=1);

namespace JSONAPI\Test\Exception\Driver;

use JSONAPI\Driver\AnnotationDriver;
use JSONAPI\Exception\Driver\AnnotationMisplace;
use JSONAPI\Test\Resources\Invalid\BadAnnotationPlacement;
use PHPUnit\Framework\TestCase;

class AnnotationMisplaceTest extends TestCase
{



    public function testConstruct()
    {
        $e = new AnnotationMisplace('someMethod', 'MyClass');
        $this->assertInstanceOf(AnnotationMisplace::class, $e);
        $this->assertStringContainsString('someMethod', $e->getMessage());
        $this->assertStringContainsString('MyClass', $e->getMessage());
    }
    public function testBadAnnotationPlacementException()
    {
        $this->expectException(AnnotationMisplace::class);
        $driver = new AnnotationDriver();
        $driver->getClassMetadata(get_class(new BadAnnotationPlacement()));
    }
}
