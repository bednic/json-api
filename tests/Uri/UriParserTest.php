<?php

namespace JSONAPI\Test\Uri;

use Doctrine\Common\Cache\ArrayCache;
use JSONAPI\Driver\SchemaDriver;
use JSONAPI\Metadata\MetadataFactory;
use JSONAPI\Metadata\MetadataRepository;
use JSONAPI\Uri\Fieldset\FieldsetInterface;
use JSONAPI\Uri\Filtering\CriteriaFilterParser;
use JSONAPI\Uri\Filtering\FilterInterface;
use JSONAPI\Uri\Inclusion\InclusionInterface;
use JSONAPI\Uri\Pagination\LimitOffsetPagination;
use JSONAPI\Uri\Pagination\PagePagination;
use JSONAPI\Uri\Pagination\PaginationInterface;
use JSONAPI\Uri\Path\PathInterface;
use JSONAPI\Uri\Sorting\SortInterface;
use JSONAPI\Uri\UriParser;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Roave\DoctrineSimpleCache\SimpleCacheAdapter;
use Slim\Psr7\Factory\ServerRequestFactory;

class UriParserTest extends TestCase
{
    private static MetadataRepository $mr;

    public static function setUpBeforeClass(): void
    {
        self::$mr = MetadataFactory::create(
            RESOURCES . '/valid',
            new SimpleCacheAdapter(new ArrayCache()),
            new SchemaDriver()
        );
    }

    public function testSetMetadata()
    {
        $this->expectNotToPerformAssertions();
        $request = ServerRequestFactory::createFromGlobals();
        $up = new UriParser($request);
        $meta = new MetadataRepository();
        $up->setMetadata($meta);
    }

    public function testGetFieldset()
    {
        $request = ServerRequestFactory::createFromGlobals();
        $up = new UriParser($request);
        $this->assertInstanceOf(FieldsetInterface::class, $up->getFieldset());
    }

    public function testGetFilter()
    {
        $request = ServerRequestFactory::createFromGlobals();
        $up = new UriParser($request);
        $this->assertInstanceOf(FilterInterface::class, $up->getFilter());
    }

    public function testGetSort()
    {
        $request = ServerRequestFactory::createFromGlobals();
        $up = new UriParser($request);
        $this->assertInstanceOf(SortInterface::class, $up->getSort());
    }

    public function testConstruct()
    {
        $request = ServerRequestFactory::createFromGlobals();
        $up = new UriParser($request, new CriteriaFilterParser(), new PagePagination(), self::$mr, new NullLogger());
        $this->assertInstanceOf(UriParser::class, $up);
    }

    public function testIsCollection()
    {
        $request = ServerRequestFactory::createFromGlobals();
        $up = new UriParser($request);
        $up->setMetadata(self::$mr);
        $this->assertFalse($up->isCollection());
    }

    public function testGetInclusion()
    {
        $request = ServerRequestFactory::createFromGlobals();
        $up = new UriParser($request);
        $this->assertInstanceOf(InclusionInterface::class, $up->getInclusion());
    }

    public function testGetPath()
    {
        $request = ServerRequestFactory::createFromGlobals();
        $up = new UriParser($request);
        $this->assertInstanceOf(PathInterface::class, $up->getPath());
    }

    public function testSetFilterParser()
    {
        $this->expectNotToPerformAssertions();
        $request = ServerRequestFactory::createFromGlobals();
        $up = new UriParser($request);
        $parser = new CriteriaFilterParser();
        $up->setFilterParser($parser);
    }

    public function testGetPagination()
    {
        $request = ServerRequestFactory::createFromGlobals();
        $up = new UriParser($request);
        $this->assertInstanceOf(PaginationInterface::class, $up->getPagination());
    }

    public function testSetPaginationParser()
    {
        $this->expectNotToPerformAssertions();
        $request = ServerRequestFactory::createFromGlobals();
        $up = new UriParser($request);
        $parser = new LimitOffsetPagination();
        $up->setPaginationParser($parser);
    }

    public function testGetRelationshipType()
    {
        $_SERVER["REQUEST_URI"] = "/getter/uuid/relationships/relation";
        $request = ServerRequestFactory::createFromGlobals();
        $up = new UriParser($request);
        $up->setMetadata(self::$mr);
        $this->assertEquals('relation', $up->getRelationshipType());
    }

    public function testGetPrimaryResourceType()
    {
        $request = ServerRequestFactory::createFromGlobals();
        $up = new UriParser($request);
        $up->setMetadata(self::$mr);
        $this->assertEquals('getter', $up->getPrimaryResourceType());
    }
}
