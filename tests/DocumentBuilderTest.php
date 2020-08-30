<?php

declare(strict_types=1);

namespace JSONAPI\Test;

use Doctrine\Common\Cache\ArrayCache;
use JSONAPI\Config;
use JSONAPI\Document\Document;
use JSONAPI\DocumentBuilder;
use JSONAPI\DocumentBuilderFactory;
use JSONAPI\Driver\AnnotationDriver;
use JSONAPI\Metadata\MetadataFactory;
use JSONAPI\Metadata\MetadataRepository;
use JSONAPI\Test\Resources\Valid\GettersExample;
use JSONAPI\Uri\LinkFactory;
use PHPUnit\Framework\TestCase;
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
    private static Schema $schema;
    private static string $baseUrl;
    /**
     * @var DocumentBuilderFactory
     */
    private static DocumentBuilderFactory $factory;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::$mr      = MetadataFactory::create(
            [RESOURCES . '/valid'],
            new SimpleCacheAdapter(new ArrayCache()),
            new AnnotationDriver()
        );
        self::$schema  = Schema::import(json_decode(file_get_contents(__DIR__ . '/../src/Middleware/schema.json')));
        self::$baseUrl = 'http://unit.test.org/api';
        self::$factory = new DocumentBuilderFactory(self::$mr, self::$baseUrl);
    }

    public function testCreate()
    {
        $request = ServerRequestFactory::createFromGlobals();
        $db      = self::$factory->new($request);
        $this->assertInstanceOf(DocumentBuilder::class, $db);
    }

    public function testSetTotal()
    {

        $request = ServerRequestFactory::createFromGlobals();
        $db      = self::$factory->new($request);
        $this->assertInstanceOf(
            DocumentBuilder::class,
            $db->setTotal(10)
        );
    }

    public function testSetData()
    {
        $request = ServerRequestFactory::createFromGlobals();
        $single  = new GettersExample('uuid');
        $db      = self::$factory->new($request);
        $this->assertInstanceOf(DocumentBuilder::class, $db->setData($single));

        $_SERVER['REQUEST_URI'] = 'getter';
        $request                = ServerRequestFactory::createFromGlobals();
        $collection             = [new GettersExample('uuid')];
        $db                     = self::$factory->new($request);
        $this->assertInstanceOf(DocumentBuilder::class, $db->setData($collection));
    }

    public function testBuild()
    {
        $request = ServerRequestFactory::createFromGlobals();
        $single  = new GettersExample('uuid');
        $db      = self::$factory->new($request);
        $doc     = $db->setData($single)->build();
        $this->assertInstanceOf(Document::class, $doc);
        $this->assertTrue($this->isValid($doc));

        $_SERVER['REQUEST_URI'] = 'getter';
        $request                = ServerRequestFactory::createFromGlobals();
        $collection             = [new GettersExample('uuid')];
        $db                     = self::$factory->new($request);
        $doc                    = $db->setData($collection)->build();
        $self                   = $doc->getLinks()[LinkFactory::SELF];
        $this->assertStringContainsString('http://unit.test.org/api', (string)$self->getData());
        $this->assertInstanceOf(Document::class, $doc);
        $this->assertTrue($this->isValid($doc));
    }

    private function isValid(Document $document): bool
    {
        self::$schema->in(json_decode(json_encode($document)));
        return true;
    }
}
