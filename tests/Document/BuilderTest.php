<?php

declare(strict_types=1);

namespace JSONAPI\Test\Document;

use JSONAPI\Configuration;
use JSONAPI\Data\Collection;
use JSONAPI\Document\Builder;
use JSONAPI\Document\BuilderFactory;
use JSONAPI\Document\Document;
use JSONAPI\Document\Link;
use JSONAPI\Driver\AnnotationDriver;
use JSONAPI\Metadata\MetadataFactory;
use JSONAPI\Metadata\MetadataRepository;
use JSONAPI\Test\Resources\Valid\GettersExample;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Slim\Psr7\Factory\ServerRequestFactory;
use Swaggest\JsonSchema\Schema;
use Swaggest\JsonSchema\SchemaContract;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Psr16Cache;

class BuilderTest extends TestCase
{
    private static MetadataRepository $mr;
    /**
     * @var SchemaContract
     */
    private static SchemaContract $schema;
    private static string $baseUrl;
    /**
     * @var BuilderFactory
     */
    private static BuilderFactory $factory;
    /**
     * @var Configuration configuration
     */
    private static Configuration $configuration;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::$mr = MetadataFactory::create(
            [RESOURCES . '/valid'],
            new Psr16Cache(new ArrayAdapter()),
            new AnnotationDriver()
        );
        self::$baseUrl = 'http://unit.test.org/api';
        self::$configuration = new Configuration(
            self::$mr,
            self::$baseUrl
        );
        self::$schema = Schema::import(json_decode(file_get_contents(__DIR__ . '/../../src/Middleware/out.json')));
        self::$factory = new BuilderFactory(self::$configuration);
    }

    public function testCreate()
    {
        $request = ServerRequestFactory::createFromGlobals();
        $db = self::$factory->create($request);
        $this->assertInstanceOf(Builder::class, $db);
    }

    public function testSetTotal()
    {
        $request = ServerRequestFactory::createFromGlobals();
        $db = self::$factory->create($request);
        $this->assertInstanceOf(
            Builder::class,
            $db->setTotal(10)
        );
    }

    public function testSetData()
    {
        $request = ServerRequestFactory::createFromGlobals();
        $single = new GettersExample('uuid');
        $db = self::$factory->create($request);
        $this->assertInstanceOf(Builder::class, $db->setData($single));

        $_SERVER['REQUEST_URI'] = 'getter';
        $request = ServerRequestFactory::createFromGlobals();
        $collection = [new GettersExample('uuid')];
        $db = self::$factory->create($request);
        $this->assertInstanceOf(Builder::class, $db->setData($collection));
    }

    public function testBuild()
    {
        new ReflectionClass(Collection::class);
        $request = ServerRequestFactory::createFromGlobals();
        $single = new GettersExample('uuid');
        $db = self::$factory->create($request);
        $doc = $db->setData($single)->build();
        $this->assertInstanceOf(Document::class, $doc);
        $this->assertTrue($this->isValid($doc));

        $_SERVER['REQUEST_URI'] = 'getter';
        $request = ServerRequestFactory::createFromGlobals();
        $collection = [new GettersExample('uuid')];
        $db = self::$factory->create($request);
        $doc = $db->setData($collection)->build();
        $self = $doc->getLinks()[Link::SELF];
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
