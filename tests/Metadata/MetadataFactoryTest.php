<?php

declare(strict_types=1);

namespace JSONAPI\Test\Metadata;

use Doctrine\Common\Cache\ArrayCache;
use JSONAPI\Driver\AnnotationDriver;
use JSONAPI\Driver\SchemaDriver;
use JSONAPI\Factory\MetadataFactory;
use JSONAPI\Metadata\MetadataRepository;
use PHPUnit\Framework\TestCase;
use Roave\DoctrineSimpleCache\SimpleCacheAdapter;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Psr16Cache;

class MetadataFactoryTest extends TestCase
{

    public function testCreate()
    {
        $repository = MetadataFactory::create(
            [RESOURCES . '/valid'],
            new Psr16Cache(new ArrayAdapter()),
            new AnnotationDriver()
        );
        $this->assertInstanceOf(MetadataRepository::class, $repository);
        $repository = MetadataFactory::create(
            [RESOURCES . '/valid'],
            new Psr16Cache(new ArrayAdapter()),
            new SchemaDriver()
        );
        $this->assertInstanceOf(MetadataRepository::class, $repository);
    }
}
