<?php

declare(strict_types=1);

namespace JSONAPI\Test;

use Doctrine\Common\Cache\ArrayCache;
use JSONAPI\Document\Document;
use JSONAPI\Document\Id;
use JSONAPI\Document\ResourceCollection;
use JSONAPI\Document\ResourceObject;
use JSONAPI\Document\ResourceObjectIdentifier;
use JSONAPI\Document\Type;
use JSONAPI\DocumentBuilder;
use JSONAPI\Driver\AnnotationDriver;
use JSONAPI\Metadata\MetadataFactory;
use JSONAPI\Metadata\MetadataRepository;
use JSONAPI\Test\Resources\Valid\GettersExample;
use JSONAPI\Uri\Filtering\CriteriaFilterParser;
use JSONAPI\Uri\Pagination\LimitOffsetPagination;
use JSONAPI\Uri\Pagination\PagePagination;
use JSONAPI\Uri\UriParser;
use Opis\JsonSchema\ISchema;
use Opis\JsonSchema\Schema;
use Opis\JsonSchema\Validator;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Roave\DoctrineSimpleCache\SimpleCacheAdapter;
use Slim\Psr7\Factory\ServerRequestFactory;

/**
 * Class DocumentBuilderTest
 *
 * @package JSONAPI\Test
 */
class DocumentBuilderTest extends TestCase
{
    private static MetadataRepository $mr;
    private static ISchema $schema;
    private static Validator $validator;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass(); // TODO: Change the autogenerated stub
        self::$mr = MetadataFactory::create(
            RESOURCES . '/valid',
            new SimpleCacheAdapter(new ArrayCache()),
            new AnnotationDriver()
        );
        self::$validator = new Validator();
        self::$schema = Schema::fromJsonString(file_get_contents(RESOURCES . '/schema.json'));
    }

    public function testGetUriParser()
    {

        $request = ServerRequestFactory::createFromGlobals();
        $db = new DocumentBuilder(self::$mr, $request);
        $this->assertInstanceOf(UriParser::class, $db->getUriParser());
    }

    public function testConstruct()
    {

        $request = ServerRequestFactory::createFromGlobals();
        $db = new DocumentBuilder(self::$mr, $request);
        $this->assertInstanceOf(DocumentBuilder::class, $db);
        $db = new DocumentBuilder(self::$mr, $request, null, null, null);
        $this->assertInstanceOf(DocumentBuilder::class, $db);
        $db = new DocumentBuilder(
            self::$mr,
            $request,
            new NullLogger(),
            new CriteriaFilterParser(),
            new LimitOffsetPagination()
        );
        $this->assertInstanceOf(DocumentBuilder::class, $db);
        $db = new DocumentBuilder(
            self::$mr,
            $request,
            new NullLogger(),
            new CriteriaFilterParser(),
            new PagePagination(),
            -1,
            25
        );
        $this->assertInstanceOf(DocumentBuilder::class, $db);
    }

    public function testSetTotalItems()
    {

        $request = ServerRequestFactory::createFromGlobals();
        $db = new DocumentBuilder(self::$mr, $request);
        $this->assertInstanceOf(DocumentBuilder::class, $db->setTotalItems(10));
    }

    public function testSetData()
    {
        $request = ServerRequestFactory::createFromGlobals();
        $single = new GettersExample('uuid');
        $db = new DocumentBuilder(self::$mr, $request);
        $this->assertInstanceOf(DocumentBuilder::class, $db->setData($single));

        $_SERVER['REQUEST_URI'] = 'getter';
        $request = ServerRequestFactory::createFromGlobals();
        $collection = [new GettersExample('uuid')];
        $db = new DocumentBuilder(self::$mr, $request);
        $this->assertInstanceOf(DocumentBuilder::class, $db->setData($collection));
    }

    public function testBuild()
    {
        $request = ServerRequestFactory::createFromGlobals();
        $single = new GettersExample('uuid');
        $db = new DocumentBuilder(self::$mr, $request);
        $doc = $db->setData($single)->build();
        $this->assertInstanceOf(Document::class, $doc);
        $this->assertTrue($this->isValid($doc));

        $_SERVER['REQUEST_URI'] = 'getter';
        $request = ServerRequestFactory::createFromGlobals();
        $collection = [new GettersExample('uuid')];
        $db = new DocumentBuilder(self::$mr, $request);
        $doc = $db->setData($collection)->build();
        $this->assertInstanceOf(Document::class, $doc);
        $this->assertTrue($this->isValid($doc));
    }
    private function isValid(Document $document): bool
    {
        $result = self::$validator->schemaValidation(json_decode(json_encode($document)), self::$schema);
        return $result->isValid();
    }
}
