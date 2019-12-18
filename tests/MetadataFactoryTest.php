<?php

namespace JSONAPI\Metadata;

use Doctrine\Common\Cache\ArrayCache;
use JSONAPI\Driver\AnnotationDriver;
use JSONAPI\Test\GettersExample;
use PHPUnit\Framework\TestCase;
use Roave\DoctrineSimpleCache\SimpleCacheAdapter;

class MetadataFactoryTest extends TestCase
{
    /**
     * @depends testConstruct
     */
    public function testGetMetadataByClass(MetadataFactory $factory)
    {
        $metadata = $factory->getMetadataByClass(GettersExample::class);
        $this->assertInstanceOf(ClassMetadata::class, $metadata);
        $this->assertEquals(GettersExample::class, $metadata->getClassName());
    }

    /**
     * @depends testConstruct
     */
    public function testGetMetadataClassByType(MetadataFactory $factory)
    {
        $metadata = $factory->getMetadataClassByType('getter');
        $this->assertInstanceOf(ClassMetadata::class, $metadata);
        $this->assertEquals('getter', $metadata->getResource()->type);
    }

    public function testConstruct()
    {
        $factory = new MetadataFactory(
            __DIR__ . '/resources',
            new SimpleCacheAdapter(new ArrayCache())
        );
        $this->assertInstanceOf(MetadataFactory::class, $factory);
        return $factory;
    }

    /**
     * @depends testConstruct
     */
    public function testGetAllMetadata(MetadataFactory $factory)
    {
        $this->assertIsArray($factory->getAllMetadata());
        $this->assertGreaterThan(0, $factory->getAllMetadata());
    }
}
