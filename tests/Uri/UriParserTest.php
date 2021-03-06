<?php

declare(strict_types=1);

namespace JSONAPI\Test\Uri;

use Doctrine\Common\Cache\ArrayCache;
use JSONAPI\Driver\SchemaDriver;
use JSONAPI\Metadata\MetadataFactory;
use JSONAPI\Metadata\MetadataRepository;
use JSONAPI\Uri\Fieldset\FieldsetInterface;
use JSONAPI\Uri\Filtering\ExpressionFilterParser;
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
    private static string $baseURL;

    public static function setUpBeforeClass(): void
    {
        self::$mr      = MetadataFactory::create(
            [RESOURCES . '/valid'],
            new SimpleCacheAdapter(new ArrayCache()),
            new SchemaDriver()
        );
        self::$baseURL = 'http://unit.test.org';
    }

    public function testGetFieldset()
    {
        $request = ServerRequestFactory::createFromGlobals();
        $up      = new UriParser($request, self::$mr, self::$baseURL);
        $this->assertInstanceOf(FieldsetInterface::class, $up->getFieldset());
    }

    public function testGetFilter()
    {
        $request = ServerRequestFactory::createFromGlobals();
        $up      = new UriParser($request, self::$mr, self::$baseURL);
        $this->assertInstanceOf(FilterInterface::class, $up->getFilter());
    }

    public function testGetSort()
    {
        $request = ServerRequestFactory::createFromGlobals();
        $up      = new UriParser($request, self::$mr, self::$baseURL);
        $this->assertInstanceOf(SortInterface::class, $up->getSort());
    }

    public function testConstruct()
    {
        $request = ServerRequestFactory::createFromGlobals();
        $up      = new UriParser(
            $request,
            self::$mr,
            self::$baseURL,
            true,
            true,
            true,
            new ExpressionFilterParser(),
            new PagePagination(),
            new NullLogger()
        );
        $this->assertInstanceOf(UriParser::class, $up);
    }

    public function testGetInclusion()
    {
        $request = ServerRequestFactory::createFromGlobals();
        $up      = new UriParser($request, self::$mr, self::$baseURL);
        $this->assertInstanceOf(InclusionInterface::class, $up->getInclusion());
    }

    public function testGetPath()
    {
        $request = ServerRequestFactory::createFromGlobals();
        $up      = new UriParser($request, self::$mr, self::$baseURL);
        $this->assertInstanceOf(PathInterface::class, $up->getPath());
    }

    public function testSetFilterParser()
    {
        $this->expectNotToPerformAssertions();
        $request = ServerRequestFactory::createFromGlobals();
        $up      = new UriParser($request, self::$mr, self::$baseURL);
        $parser  = new ExpressionFilterParser();
        $up->setFilterParser($parser);
    }

    public function testGetPagination()
    {
        $request = ServerRequestFactory::createFromGlobals();
        $up      = new UriParser($request, self::$mr, self::$baseURL);
        $this->assertInstanceOf(PaginationInterface::class, $up->getPagination());
    }

    public function testSetPaginationParser()
    {
        $this->expectNotToPerformAssertions();
        $request = ServerRequestFactory::createFromGlobals();
        $up      = new UriParser($request, self::$mr, self::$baseURL);
        $parser  = new LimitOffsetPagination();
        $up->setPaginationParser($parser);
    }
}
