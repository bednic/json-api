<?php

declare(strict_types=1);

namespace JSONAPI\Test\Driver;

use JSONAPI\Driver\SchemaDriver;
use JSONAPI\Metadata\ClassMetadata;
use JSONAPI\Test\Resources\Valid\GettersExample;
use JSONAPI\Test\Resources\Valid\MetaExample;
use JSONAPI\Test\Resources\Valid\PropsExample;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class SchemaDriverTest extends TestCase
{
    public function testGetClassMetadata()
    {
        $driver = new SchemaDriver();
        $metadata = $driver->getClassMetadata(GettersExample::class);
        $this->assertInstanceOf(ClassMetadata::class, $metadata);
        $metadata = $driver->getClassMetadata(PropsExample::class);
        $this->assertInstanceOf(ClassMetadata::class, $metadata);
        $metadata = $driver->getClassMetadata(MetaExample::class);
        $this->assertInstanceOf(ClassMetadata::class, $metadata);
    }

    public function testConstruct()
    {
        $driver = new SchemaDriver();
        $this->assertInstanceOf(SchemaDriver::class, $driver);
        $driver = new SchemaDriver(new NullLogger());
        $this->assertInstanceOf(SchemaDriver::class, $driver);
    }

    public function testIsCollection()
    {
        $driver = new SchemaDriver();
        $metadata = $driver->getClassMetadata(GettersExample::class);
        $this->assertTrue($metadata->getRelationship('collection')->isCollection);
        $this->assertFalse($metadata->getRelationship('relation')->isCollection);
    }
}
