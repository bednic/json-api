<?php

namespace JSONAPI\Document;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Collections\Criteria;
use JSONAPI\Metadata\Encoder;
use JSONAPI\Metadata\MetadataFactory;
use JSONAPI\Test\GettersExample;
use JSONAPI\Uri\Fieldset\FieldsetInterface;
use JSONAPI\Uri\Fieldset\SortParser;
use JSONAPI\Uri\Filtering\CriteriaFilterParser;
use JSONAPI\Uri\Filtering\FilterInterface;
use JSONAPI\Uri\Inclusion\InclusionInterface;
use JSONAPI\Uri\Pagination\LimitOffsetPagination;
use JSONAPI\Uri\Pagination\PagePagination;
use JSONAPI\Uri\Pagination\PaginationInterface;
use JSONAPI\Uri\Path\PathInterface;
use JSONAPI\Uri\Sorting\SortInterface;
use Opis\JsonSchema\Schema;
use Opis\JsonSchema\Validator;
use PHPUnit\Framework\TestCase;
use Roave\DoctrineSimpleCache\SimpleCacheAdapter;
use Slim\Psr7\Factory\ServerRequestFactory;

class DocumentTest extends TestCase
{

    private static MetadataFactory $factory;
    private static Schema $schema;
    private static Validator $validator;

    public static function setUpBeforeClass(): void
    {
        $cache = new SimpleCacheAdapter(new ArrayCache());
        self::$factory = new MetadataFactory(__DIR__ . '/resources/', $cache);
        self::$schema = Schema::fromJsonString(file_get_contents(__DIR__ . '/../schema.json'));
        self::$validator = new Validator();
    }

    public function testSetCollection()
    {
        $_SERVER["REQUEST_URI"] = "/getter";
        $request = ServerRequestFactory::createFromGlobals();
        $document = new Document(self::$factory, $request);
        $resource1 = new GettersExample('1');
        $resource2 = new GettersExample('2');
        $collection = [$resource1, $resource2];
        $document->setCollection($collection, 2);
        $this->assertTrue($this->isValidJsonApiDocument($document));
    }

    public function testGetFieldset()
    {
        $_SERVER["REQUEST_URI"] = "/resource?fields[resource]=publicProperty,readOnlyProperty";
        $request = ServerRequestFactory::createFromGlobals();
        $document = new Document(self::$factory, $request);
        $this->assertInstanceOf(FieldsetInterface::class, $document->getFieldset());
        $this->assertTrue($document->getFieldset()->showField('resource', 'publicProperty'));
        $this->assertTrue($document->getFieldset()->showField('resource', 'readOnlyProperty'));
        $this->assertFalse($document->getFieldset()->showField('resource', 'privateProperty'));
        $this->assertTrue($document->getFieldset()->showField('random', 'property'));
        $this->assertTrue($this->isValidJsonApiDocument($document));
    }

    public function testToString()
    {
        $request = ServerRequestFactory::createFromGlobals();
        $document = new Document(self::$factory, $request);
        $this->assertTrue(is_string($document->__toString()));
        $this->assertTrue($this->isValidJsonApiDocument($document));
    }

    public function testGetInclusion()
    {
        $_SERVER["REQUEST_URI"] = "/resource?include=relations";
        $request = ServerRequestFactory::createFromGlobals();
        $document = new Document(self::$factory, $request);
        $this->assertInstanceOf(InclusionInterface::class, $document->getInclusion());
        $this->assertIsArray($document->getInclusion()->getInclusions());
        $this->assertCount(1, $document->getInclusion()->getInclusions());
        $this->assertEquals('relations', $document->getInclusion()->getInclusions()[0]->getRelationName());
        $this->assertFalse($document->getInclusion()->getInclusions()[0]->hasInclusions());
        $this->assertEmpty($document->getInclusion()->getInclusions()[0]->getInclusions());
        $this->assertTrue($this->isValidJsonApiDocument($document));
    }

    public function testGetFilter()
    {
        $_SERVER["REQUEST_URI"] = "/resource?filter=publicProperty eq 'public-value'";
        $request = ServerRequestFactory::createFromGlobals();
        $document = new Document(self::$factory, $request);
        $this->assertInstanceOf(FilterInterface::class, $document->getFilter());
        $this->assertInstanceOf(Criteria::class, $document->getFilter()->getCondition());
        $expr = Criteria::create()->where(Criteria::expr()->eq('publicProperty', 'public-value'));
        $this->assertEquals($expr, $document->getFilter()->getCondition());
        $this->assertTrue($this->isValidJsonApiDocument($document));
    }

    public function testGetPagination()
    {
        $_SERVER["REQUEST_URI"] = "/resource?page[offset]=10&page[limit]=5";
        $request = ServerRequestFactory::createFromGlobals();
        $document = new Document(self::$factory, $request);
        $this->assertInstanceOf(PaginationInterface::class, $document->getPagination());
        $this->assertInstanceOf(LimitOffsetPagination::class, $document->getPagination());
        $document->getPagination()->setTotal(100);
        $this->assertEquals(10, $document->getPagination()->getOffset());
        $this->assertEquals(5, $document->getPagination()->getLimit());
        $this->assertInstanceOf(PaginationInterface::class, $document->getPagination()->first());
        $this->assertEquals(0, $document->getPagination()->first()->getOffset());
        $this->assertEquals(5, $document->getPagination()->first()->getLimit());
        $this->assertInstanceOf(PaginationInterface::class, $document->getPagination()->last());
        $this->assertEquals(95, $document->getPagination()->last()->getOffset());
        $this->assertEquals(5, $document->getPagination()->last()->getLimit());
        $this->assertInstanceOf(PaginationInterface::class, $document->getPagination()->prev());
        $this->assertEquals(5, $document->getPagination()->prev()->getOffset());
        $this->assertEquals(5, $document->getPagination()->prev()->getLimit());
        $this->assertInstanceOf(PaginationInterface::class, $document->getPagination()->next());
        $this->assertEquals(15, $document->getPagination()->next()->getOffset());
        $this->assertEquals(5, $document->getPagination()->next()->getLimit());
        $this->assertTrue($this->isValidJsonApiDocument($document));
    }

    public function testGetData()
    {
        $request = ServerRequestFactory::createFromGlobals();
        $document = new Document(self::$factory, $request);
        $object = new GettersExample('uuid');
        $document->setResource($object);
        $this->assertTrue($this->isValidJsonApiDocument($document));
        $resource = $document->getData();
        $this->assertEquals($object->getId(), $resource->getId());
    }

    public function testConstruct()
    {
        $request = ServerRequestFactory::createFromGlobals();
        $document = new Document(self::$factory, $request);
        $this->assertInstanceOf(Document::class, $document);
        $this->assertTrue($this->isValidJsonApiDocument($document));
    }

    public function testLoadRequestData()
    {
        $body = [
            'data' => [
                'id' => 'uuid',
                'type' => 'prop',
                'attributes' => [
                    'stringProperty' => 'string',
                    'intProperty' => '1',
                    'arrayProperty' => [4, 5, 6],
                    'dtoProperty' => [
                        'stringProperty' => 'string',
                        'intProperty' => 123,
                        'boolProperty' => false
                    ]
                ],
                'relationships' => [
                    'collection' => [
                        'data' => [
                            [
                                'id' => 'rel1',
                                'type' => 'relation'
                            ],
                            [
                                'id' => 'rel2',
                                'type' => 'relation'
                            ]
                        ],
                    ],
                    'relation' => [
                        'data' => [
                            'id' => 'rel1',
                            'type' => 'relation'
                        ]
                    ]
                ]
            ]
        ];
        $body = json_decode(json_encode($body));
        $_SERVER["REQUEST_URI"] = "/prop/uuid";
        $request = ServerRequestFactory::createFromGlobals();
        $document = new Document(self::$factory, $request);
        $document->loadRequestData($body);
        $this->assertInstanceOf(ResourceObject::class, $document->getData());
        $this->assertEquals('uuid', $document->getData()->getId());
        $this->assertEquals('prop', $document->getData()->getType());
        $this->assertEquals('string', $document->getData()->getAttribute('stringProperty')->getData());
    }

    public function testSetFilterParser()
    {
        $request = ServerRequestFactory::createFromGlobals();
        $document = new Document(self::$factory, $request);
        $goodParser = new CriteriaFilterParser();
        $document->setFilterParser($goodParser);
        $badParser = new SortParser();
        $this->expectException(\TypeError::class);
        $document->setFilterParser($badParser);
    }

    public function testGetSort()
    {
        $_SERVER['REQUEST_URI'] = '/resource?sort=publicProperty';
        $request = ServerRequestFactory::createFromGlobals();
        $document = new Document(self::$factory, $request);
        $this->assertInstanceOf(SortInterface::class, $document->getSort());
        $this->assertIsArray($document->getSort()->getOrder());
        $this->assertEquals(SortInterface::ASC, $document->getSort()->getOrder()['publicProperty']);
        $this->assertTrue($this->isValidJsonApiDocument($document));
    }

    public function testGetPath()
    {
        $_SERVER['REQUEST_URI'] = '/resource/id/relationships/relations';
        $request = ServerRequestFactory::createFromGlobals();
        $document = new Document(self::$factory, $request);
        $this->assertInstanceOf(PathInterface::class, $document->getPath());
        //todo: own unit test, it does not belong here
        //        $this->assertEquals('resource', $document->getPath()->getResourceType());
        //        $this->assertEquals('id', $document->getPath()->getId());
        //        $this->assertEquals('resource-relation', $document->getPath()->getRelationshipType());
        //        $this->assertEquals('resource-relation', $document->getPath()->getPrimaryResourceType());
        //        $this->assertTrue($document->getPath()->isRelationship());
        //        $this->assertTrue($this->isValidJsonApiDocument($document));
    }

    public function testAddLink()
    {
        $_SERVER['REQUEST_URI'] = '/resource';
        $request = ServerRequestFactory::createFromGlobals();
        $document = new Document(self::$factory, $request);
        $link = new Link('foo', 'http://bar.com');
        $document->addLink($link);
        $data = $document->jsonSerialize();
        $this->assertEquals('http://bar.com', $data['links']['foo']->getData());
        $this->assertTrue($this->isValidJsonApiDocument($document));
    }

    public function testAddError()
    {
        $_SERVER['REQUEST_URI'] = '/resource';
        $request = ServerRequestFactory::createFromGlobals();
        $document = new Document(self::$factory, $request);
        $error = new Error();
        $error->setTitle("Test Error");
        $error->setId("unique-id");
        $document->addError($error);
        $data = $document->jsonSerialize();
        $this->assertCount(1, $data['errors']);
        $this->assertInstanceOf(Error::class, $data['errors'][0]);
        $this->assertTrue($this->isValidJsonApiDocument($document));
    }

    public function testGetEncoder()
    {
        $_SERVER['REQUEST_URI'] = '/resource';
        $request = ServerRequestFactory::createFromGlobals();
        $document = new Document(self::$factory, $request);
        $this->assertInstanceOf(Encoder::class, $document->getEncoder());
        $this->assertTrue($this->isValidJsonApiDocument($document));
    }

    public function testJsonSerialize()
    {
        $_SERVER['REQUEST_URI'] = '/resource';
        $request = ServerRequestFactory::createFromGlobals();
        $document = new Document(self::$factory, $request);
        $this->assertInstanceOf(\JsonSerializable::class, $document);
        $this->assertIsArray($document->jsonSerialize());
        $this->assertNotEmpty(json_encode($document));
        $this->assertTrue($this->isValidJsonApiDocument($document));
    }

    public function testSetResource()
    {
        $_SERVER['REQUEST_URI'] = '/getter/uuid';
        $request = ServerRequestFactory::createFromGlobals();
        $document = new Document(self::$factory, $request);
        $object = new GettersExample('uuid');
        $document->setResource($object);
        $resource = $document->getData();
        $this->assertEquals($object->getId(), $resource->getId());
        $this->assertTrue($this->isValidJsonApiDocument($document));
    }

    public function testSetPaginationParser()
    {
        $request = ServerRequestFactory::createFromGlobals();
        $document = new Document(self::$factory, $request);
        $goodParser = new LimitOffsetPagination();
        $document->setPaginationParser($goodParser);
        $goodParser = new PagePagination();
        $document->setPaginationParser($goodParser);
        $this->expectException(\TypeError::class);
        $badParser = new \stdClass();
        $document->setPaginationParser($badParser);
    }

    private function isValidJsonApiDocument(Document $document)
    {
        ini_set('xdebug.var_display_max_depth', 10);
        $data = json_decode(json_encode($document));
        $result = self::$validator->schemaValidation($data, self::$schema);
        if (!$result->isValid()) {
            var_dump($result->getFirstError()->keyword());
        }
        return $result->isValid();
    }
}
