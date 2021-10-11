<?php

declare(strict_types=1);

namespace JSONAPI\Test\URI;

use JSONAPI\Configuration;
use JSONAPI\Driver\SchemaDriver;
use JSONAPI\Metadata\MetadataFactory;
use JSONAPI\Metadata\MetadataRepository;
use JSONAPI\URI\Fieldset\FieldsetInterface;
use JSONAPI\URI\Filtering\ExpressionFilterParser;
use JSONAPI\URI\Filtering\FilterInterface;
use JSONAPI\URI\Inclusion\InclusionInterface;
use JSONAPI\URI\Pagination\LimitOffsetPagination;
use JSONAPI\URI\Pagination\PagePagination;
use JSONAPI\URI\Pagination\PaginationInterface;
use JSONAPI\URI\Path\PathInterface;
use JSONAPI\URI\Sorting\SortInterface;
use JSONAPI\URI\URIParser;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Slim\Psr7\Factory\ServerRequestFactory;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Psr16Cache;

class UriParserTest extends TestCase
{
    private static MetadataRepository $mr;
    private static string $baseURL;
    /**
     * @var Configuration configuration
     */
    private static Configuration $configuration;

    public static function setUpBeforeClass(): void
    {
        self::$mr = MetadataFactory::create(
            [RESOURCES . '/valid'],
            new Psr16Cache(new ArrayAdapter()),
            new SchemaDriver()
        );
        self::$baseURL = 'http://unit.test.org';
        self::$configuration = new Configuration(self::$mr, self::$baseURL);
    }

    public function testGetFieldset()
    {
        $request = ServerRequestFactory::createFromGlobals();
        $up = (new URIParser(self::$configuration))->parse($request);
        $this->assertInstanceOf(FieldsetInterface::class, $up->getFieldset());
    }

    public function testGetFilter()
    {
        $request = ServerRequestFactory::createFromGlobals();
        $up = (new URIParser(self::$configuration))->parse($request);
        $this->assertInstanceOf(FilterInterface::class, $up->getFilter());
    }

    public function testGetSort()
    {
        $request = ServerRequestFactory::createFromGlobals();
        $up = (new URIParser(self::$configuration))->parse($request);
        $this->assertInstanceOf(SortInterface::class, $up->getSort());
    }

    public function testConstruct()
    {
        $up = new URIParser(self::$configuration);
        $this->assertInstanceOf(URIParser::class, $up);
    }

    public function testGetInclusion()
    {
        $request = ServerRequestFactory::createFromGlobals();
        $up = (new URIParser(self::$configuration))->parse($request);
        $this->assertInstanceOf(InclusionInterface::class, $up->getInclusion());
    }

    public function testGetPath()
    {
        $request = ServerRequestFactory::createFromGlobals();
        $up = (new URIParser(self::$configuration))->parse($request);
        $this->assertInstanceOf(PathInterface::class, $up->getPath());
    }

    public function testGetPagination()
    {
        $request = ServerRequestFactory::createFromGlobals();
        $up = (new URIParser(self::$configuration))->parse($request);
        $this->assertInstanceOf(PaginationInterface::class, $up->getPagination());
    }
}
