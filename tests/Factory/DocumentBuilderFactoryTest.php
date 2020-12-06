<?php

declare(strict_types=1);

namespace JSONAPI\Test\Factory;

use Doctrine\Common\Cache\ArrayCache;
use JSONAPI\Document\Builder;
use JSONAPI\Driver\AnnotationDriver;
use JSONAPI\Factory\DocumentBuilderFactory;
use JSONAPI\Factory\MetadataFactory;
use JSONAPI\Metadata\MetadataRepository;
use JSONAPI\URI\URIParser;
use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\ServerRequestFactory;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Psr16Cache;

class DocumentBuilderFactoryTest extends TestCase
{
    public static MetadataRepository $metadata;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass(); // TODO: Change the autogenerated stub
        self::$metadata = MetadataFactory::create(
            [RESOURCES . '/valid'],
            new Psr16Cache(new ArrayAdapter()),
            new AnnotationDriver()
        );
    }

    public function testNew()
    {
        $baseUrl  = 'http://unit.test.org';
        $request  = ServerRequestFactory::createFromGlobals();
        $factory  = new DocumentBuilderFactory(self::$metadata, $baseUrl);
        $composer = $factory->new($request);
        $this->assertInstanceOf(Builder::class, $composer);
    }

    public function testGetURIParser()
    {
        $baseUrl = 'http://unit.test.org';
        $request = ServerRequestFactory::createFromGlobals();
        $factory = new DocumentBuilderFactory(self::$metadata, $baseUrl);
        $this->assertInstanceOf(URIParser::class, $factory->uri($request));
    }
}
