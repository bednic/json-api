<?php

declare(strict_types=1);

namespace JSONAPI\Test;

use Doctrine\Common\Cache\ArrayCache;
use JSONAPI\Config;
use JSONAPI\Document\Document;
use JSONAPI\DocumentBuilder;
use JSONAPI\Driver\AnnotationDriver;
use JSONAPI\Metadata\MetadataFactory;
use JSONAPI\Metadata\MetadataRepository;
use JSONAPI\Test\Resources\Valid\GettersExample;
use JSONAPI\Uri\LinkFactory;
use JSONAPI\Uri\UriParser;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Roave\DoctrineSimpleCache\SimpleCacheAdapter;
use Slim\Psr7\Factory\ServerRequestFactory;
use Swaggest\JsonSchema\Schema;

/**
 * Class DocumentBuilderTest
 *
 * @package JSONAPI\Test
 */
class DocumentBuilderTest extends TestCase
{
    private static MetadataRepository $mr;
    /**
     * @var Schema
     */
    private static $schema;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass(); // TODO: Change the autogenerated stub
        self::$mr = MetadataFactory::create(
            [RESOURCES . '/valid'],
            new SimpleCacheAdapter(new ArrayCache()),
            new AnnotationDriver()
        );
        self::$schema = Schema::import(json_decode(file_get_contents(RESOURCES . '/schema.json')));
    }

    public function testCreate()
    {
        $request = ServerRequestFactory::createFromGlobals();
        $db = new DocumentBuilder(self::$mr, new UriParser($request, self::$mr));
        $this->assertInstanceOf(DocumentBuilder::class, $db);
        $db = new DocumentBuilder(self::$mr, new UriParser($request, self::$mr), null);
        $this->assertInstanceOf(DocumentBuilder::class, $db);
        $db = new DocumentBuilder(self::$mr, new UriParser($request, self::$mr), new NullLogger());
        $this->assertInstanceOf(DocumentBuilder::class, $db);
    }

    public function testSetTotalItems()
    {

        $request = ServerRequestFactory::createFromGlobals();
        $this->assertInstanceOf(
            DocumentBuilder::class,
            (new DocumentBuilder(self::$mr, new UriParser($request, self::$mr)))->setTotalItems(10)
        );
    }

    public function testSetData()
    {
        $request = ServerRequestFactory::createFromGlobals();
        $single = new GettersExample('uuid');
        $this->assertInstanceOf(
            DocumentBuilder::class,
            (new DocumentBuilder(
                self::$mr,
                new UriParser($request, self::$mr)
            ))->setData($single)
        );

        $_SERVER['REQUEST_URI'] = 'getter';
        $request = ServerRequestFactory::createFromGlobals();
        $collection = [new GettersExample('uuid')];
        $this->assertInstanceOf(
            DocumentBuilder::class,
            (new DocumentBuilder(self::$mr, new UriParser($request, self::$mr)))->setData($collection)
        );
    }

    public function testBuild()
    {
        $request = ServerRequestFactory::createFromGlobals();
        $single = new GettersExample('uuid');
        $doc = (new DocumentBuilder(self::$mr, new UriParser($request, self::$mr)))->setData($single)->build();
        $this->assertInstanceOf(Document::class, $doc);
        $this->assertTrue($this->isValid($doc));

        $_SERVER['REQUEST_URI'] = 'getter';
        $request = ServerRequestFactory::createFromGlobals();
        $collection = [new GettersExample('uuid')];
        $doc = (new DocumentBuilder(self::$mr, new UriParser($request, self::$mr)))->setData($collection)->build();
        $self = $doc->getLinks()[LinkFactory::SELF];
        $this->assertStringContainsString('http://unit.test.org/api', (string) $self->getData());
        $this->assertInstanceOf(Document::class, $doc);
        $this->assertTrue($this->isValid($doc));
    }

    private function isValid(Document $document): bool
    {
        self::$schema->in(json_decode(json_encode($document)));
        return true;
    }
}
