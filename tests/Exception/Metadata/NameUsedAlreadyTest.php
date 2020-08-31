<?php

declare(strict_types=1);

namespace JSONAPI\Test\Exception\Metadata;

use JSONAPI\Driver\AnnotationDriver;
use JSONAPI\Exception\Metadata\AlreadyInUse;
use JSONAPI\Test\Resources\Invalid\ReserveWords;
use PHPUnit\Framework\TestCase;

class NameUsedAlreadyTest extends TestCase
{


    public function testConstruct()
    {
        $e = new AlreadyInUse('someName');
        $this->assertInstanceOf(AlreadyInUse::class, $e);
        $this->assertStringContainsString('someName', $e->getMessage());
    }
    public function testUsage()
    {
        $this->expectException(AlreadyInUse::class);
        $driver = new AnnotationDriver();
        $driver->getClassMetadata(get_class(new ReserveWords()));
    }
}
