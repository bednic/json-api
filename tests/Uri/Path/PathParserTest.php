<?php

declare(strict_types=1);

namespace JSONAPI\Test\Uri\Path;

use Doctrine\Common\Cache\ArrayCache;
use Fig\Http\Message\RequestMethodInterface;
use JSONAPI\Config;
use JSONAPI\Driver\AnnotationDriver;
use JSONAPI\Metadata\MetadataFactory;
use JSONAPI\Metadata\MetadataRepository;
use JSONAPI\Uri\LinkFactory;
use JSONAPI\Uri\Path\PathInterface;
use JSONAPI\Uri\Path\PathParser;
use PHPUnit\Framework\TestCase;
use Roave\DoctrineSimpleCache\SimpleCacheAdapter;

/**
 * Class PathParserTest
 *
 * @package JSONAPI\Test\Uri\Path
 */
class PathParserTest extends TestCase
{

    /**
     * @var MetadataRepository
     */
    private static MetadataRepository $mr;
    private static string $baseUrl;

    public static function setUpBeforeClass(): void
    {
        self::$mr      = MetadataFactory::create(
            [RESOURCES . '/valid'],
            new SimpleCacheAdapter(new ArrayCache()),
            new AnnotationDriver()
        );
        self::$baseUrl = 'http://unit.test.org';
    }

    public function testGetId()
    {
        $test   = '/resource/uuid/relationships/relation';
        $parser = new PathParser(self::$mr, self::$baseUrl);
        $path   = $parser->parse($test);
        $this->assertEquals('uuid', $path->getId());
    }

    public function testGetResourceType()
    {
        $test   = '/resource/uuid/relationships/relation';
        $parser = new PathParser(self::$mr, self::$baseUrl);
        $path   = $parser->parse($test);
        $this->assertEquals('resource', $path->getResourceType());
    }

    public function testToString()
    {
        $test   = '/resource/uuid/relationships/relation';
        $parser = new PathParser(self::$mr, self::$baseUrl);
        $path   = $parser->parse($test);
        $result = (string)$path;
        $this->assertIsString($result);
        $this->assertEquals($test, $result);
    }

    public function testGetPrimaryResourceType()
    {
        $parser = new PathParser(self::$mr, self::$baseUrl);
        $data   = '/getter/uuid';
        $path   = $parser->parse($data);
        $this->assertEquals('getter', $path->getPrimaryResourceType());
    }

    public function testConstruct()
    {
        $parser = new PathParser(self::$mr, self::$baseUrl);
        $this->assertInstanceOf(PathParser::class, $parser);
    }

    public function testParse()
    {
        $parser = new PathParser(self::$mr, self::$baseUrl . '/api');
        $data   = '/api/resource/uuid';
        $path   = $parser->parse($data);
        $this->assertInstanceOf(PathInterface::class, $path);

        $this->assertEquals('resource', $path->getResourceType());
        $data = '/resource/uuid';
        $path = $parser->parse($data);
        $this->assertEquals('resource', $path->getResourceType());
    }

    public function testProxyUrl()
    {
        $parser = new PathParser(self::$mr, self::$baseUrl . '/resources');
        $data   = '/resources/somethings';
        $path   = $parser->parse($data);
        $this->assertTrue($path->isCollection());
        $this->assertEquals('somethings', $path->getResourceType());
        $data = '/resources/somethings/some-uuid';
        $path = $parser->parse($data);
        $this->assertEquals('somethings', $path->getResourceType());
        $this->assertEquals('some-uuid', $path->getId());
    }

    public function testIsRelationship()
    {
        $test   = '/resource/uuid/relationships/relation';
        $parser = new PathParser(self::$mr, self::$baseUrl);
        $path   = $parser->parse($test);
        $this->assertTrue($path->isRelationship());
    }

    public function testIsCollection()
    {
        $test   = '/resource';
        $parser = new PathParser(self::$mr, self::$baseUrl, RequestMethodInterface::METHOD_GET);
        $path   = $parser->parse($test);
        $this->assertTrue($path->isCollection());
    }

    public function testGetRelationshipName()
    {
        $test   = '/resource/uuid/relationships/relation';
        $parser = new PathParser(self::$mr, self::$baseUrl);
        $path   = $parser->parse($test);
        $this->assertEquals('relation', $path->getRelationshipName());
    }
}
