<?php

declare(strict_types=1);

namespace JSONAPI\Test\Exception\Metadata;

use JSONAPI\Driver\AnnotationDriver;
use JSONAPI\Exception\Metadata\NameUsedAlready;
use JSONAPI\Test\Resources\Invalid\ReserveWords;
use PHPUnit\Framework\TestCase;

class NameUsedAlreadyTest extends TestCase
{
    protected $runTestInSeparateProcess = true;

    public function testConstruct()
    {
        $e = new NameUsedAlready('someName');
        $this->assertInstanceOf(NameUsedAlready::class, $e);
        $this->assertStringContainsString('someName', $e->getMessage());
    }
    public function testUsage()
    {
        $this->expectException(NameUsedAlready::class);
        $driver = new AnnotationDriver();
        $driver->getClassMetadata(get_class(new ReserveWords()));
    }
}
