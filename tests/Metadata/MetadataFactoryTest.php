<?php

namespace JSONAPI\Test\Metadata;

use Doctrine\Common\Cache\ArrayCache;
use JSONAPI\Driver\AnnotationDriver;
use JSONAPI\Metadata\MetadataFactory;
use JSONAPI\Metadata\MetadataRepository;
use PHPUnit\Framework\TestCase;
use Roave\DoctrineSimpleCache\SimpleCacheAdapter;

class MetadataFactoryTest extends TestCase
{

    public function testCreate()
    {
        $repository = MetadataFactory::create(
            RESOURCES . '/valid',
            new SimpleCacheAdapter(new ArrayCache()),
            new AnnotationDriver()
        );
        $this->assertInstanceOf(MetadataRepository::class, $repository);
    }
}
