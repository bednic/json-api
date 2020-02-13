<?php

declare(strict_types=1);

namespace JSONAPI\Test\Metadata;

use Doctrine\Common\Cache\ArrayCache;
use JSONAPI\Driver\AnnotationDriver;
use JSONAPI\Driver\SchemaDriver;
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
        $repository = MetadataFactory::create(
            RESOURCES . '/valid',
            new SimpleCacheAdapter(new ArrayCache()),
            new SchemaDriver()
        );
        $this->assertInstanceOf(MetadataRepository::class, $repository);
    }
}
