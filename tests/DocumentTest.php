<?php

namespace JSONAPI\Test;

use JSONAPI\Document\Document;
use JSONAPI\Document\Error;
use JSONAPI\Document\Link;
use JSONAPI\Document\Meta;
use JSONAPI\Document\ResourceObject;
use JSONAPI\Exception\Document\BadRequest;
use JSONAPI\Metadata\MetadataFactory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class DocumentTest
 *
 * @package JSONAPI\Test
 */
class DocumentTest extends TestCase
{

    private static $factory;

    public static function setUpBeforeClass(): void
    {
        self::$factory = new MetadataFactory(__DIR__ . '/resources/');
    }

    public function testConstruct()
    {
        $document = new Document(self::$factory);
        $this->assertInstanceOf(Document::class, $document);
        return $document;
    }

    public function testAddMeta()
    {
        $document = new Document(self::$factory);
        $document->setMeta(new Meta(['count' => 1]));
        $json = json_decode(json_encode($document), true);
        $this->assertArrayHasKey('meta', $json);
        $this->assertEquals(1, $json['meta']['count']);
    }

    public function testRelationships()
    {
        $_SERVER["REQUEST_URI"] = "/resource/uuid/relationships/relations";
        $relations[] = new RelationExample('id1');
        $relations[] = new RelationExample('id2');
        $document = new Document(self::$factory);
        $document->setData($relations);
        $json = json_decode(json_encode($document), true);
        $this->assertArrayHasKey('data', $json);
        $this->assertArrayHasKey('links', $json);
        $this->assertArrayHasKey('self', $json['links']);
        $this->assertArrayHasKey('related', $json['links']);
        $this->assertEquals('http://unit.test.org/resource/uuid/relationships/relations', $json['links']['self']);
        $this->assertEquals('http://unit.test.org/resource/uuid/relations', $json['links']['related']);
        $this->assertCount(2, $json['data']);
        $this->assertEquals('id1', $json['data'][0]['id']);
        $this->assertEquals('id2', $json['data'][1]['id']);
    }

    public function testRelations()
    {
        $_SERVER["REQUEST_URI"] = "/resource/uuid/relations";
        $relations[] = new RelationExample('id1');
        $relations[] = new RelationExample('id2');
        $document = new Document(self::$factory);
        $document->setData($relations);
        $json = json_decode(json_encode($document), true);
        $this->assertArrayHasKey('data', $json);
        $this->assertArrayHasKey('links', $json);
        $this->assertArrayHasKey('self', $json['links']);
        $this->assertEquals('http://unit.test.org/resource/uuid/relations', $json['links']['self']);
    }

    public function testCollection()
    {
        $_SERVER["REQUEST_URI"] = "/resource";
        $document = new Document(self::$factory);
        $collection = [];
        $collection[] = new ObjectExample('id1');
        $collection[] = new ObjectExample('id2');
        $document->setData($collection);
        $this->assertIsArray($document->getData());
        $this->assertCount(2, $document->getData());
    }

    public function testToString()
    {
        $document = new Document(self::$factory);
        $this->assertEquals((string)$document, json_encode($document));
    }


    public function testAddLink()
    {
        $document = new Document(self::$factory);
        $document->addLink(new Link('own', 'http://my-own.link.com'));
        $document->setLinks([
            new Link('link1', 'http://link1.com')
        ]);
        $json = json_decode(json_encode($document), true);
        $this->assertArrayHasKey('links', $json);
        $this->assertEquals('http://my-own.link.com', $json['links']['own']);
        $this->assertEquals('http://link1.com', $json['links']['link1']);
    }


    public function testAddError()
    {
        $document = new Document(self::$factory);
        try {
            throw new BadRequest("Test exception");
        } catch (BadRequest $exception) {
            $error = Error::fromException($exception);
            $document->addError($error);
            $json = json_decode(json_encode($document), true);
            $this->assertArrayHasKey('errors', $json);
            $this->assertArrayNotHasKey('data', $json);
        }
    }

    public function testCreateFromRequestSingle()
    {
        $single = [
            'data' => [
                'id' => 'uuid',
                'type' => 'resource',
                'attributes' => [
                    'publicProperty' => 'public',
                    'privateProperty' => 'private',
                    'readOnlyProperty' => 'read-only'
                ],
                'relationships' => [
                    'relations' => [
                        'data' => [
                            [
                                'id' => 'rel1',
                                'type' => 'resource-relation'
                            ],
                            [
                                'id' => 'rel2',
                                'type' => 'resource-relation'
                            ]
                        ],
                        'links' => [
                            'self' => 'http://unit.test.org/resource/uuid/relationships/relations',
                            'related' => 'http://unit.test.org/resource/uuid/relations'
                        ]
                    ]
                ]
            ],
            'links' => [
                'self' => 'http://unit.test.org/resource/uuid'
            ]
        ];

        /** @var ServerRequestInterface $request */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getParsedBody')
            ->willReturn(json_decode(json_encode($single)));
        $request->method('getHeader')
            ->with('Content-Type')
            ->willReturn([Document::MEDIA_TYPE]);

        $document = Document::createFromRequest($request, self::$factory);
        $resource = $document->getData();
        $this->assertInstanceOf(Document::class, $document);
        $this->assertInstanceOf(ResourceObject::class, $resource);
        $this->assertEquals('uuid', $resource->getId());
        $this->assertEquals('resource', $resource->getType());
    }

    public function testCreateFromRequestCollection()
    {
        $collection = [
            'data' => [
                [
                    'id' => 'uuid1',
                    'type' => 'resource',
                    'attributes' => [
                        'publicProperty' => 'public',
                        'privateProperty' => 'private',
                        'readOnlyProperty' => 'read-only'
                    ],
                    'relationships' => [
                        'relations' => [
                            'data' => [
                                [
                                    'id' => 'rel1',
                                    'type' => 'resource-relation'
                                ],
                                [
                                    'id' => 'rel2',
                                    'type' => 'resource-relation'
                                ]
                            ],
                            'links' => [
                                'self' => 'http://unit.test.org/resource/uuid1/relationships/relations',
                                'related' => 'http://unit.test.org/resource/uuid1/relations'
                            ]
                        ]
                    ]
                ],
                [
                    'id' => 'uuid2',
                    'type' => 'resource',
                    'attributes' => [
                        'publicProperty' => 'public',
                        'privateProperty' => 'private',
                        'readOnlyProperty' => 'read-only'
                    ],
                    'relationships' => [
                        'relations' => [
                            'data' => [
                                [
                                    'id' => 'rel3',
                                    'type' => 'resource-relation'
                                ],
                                [
                                    'id' => 'rel4',
                                    'type' => 'resource-relation'
                                ]
                            ],
                            'links' => [
                                'self' => 'http://unit.test.org/resource/uuid2/relationships/relations',
                                'related' => 'http://unit.test.org/resource/uuid2/relations'
                            ]
                        ]
                    ]
                ]
            ],
            'links' => [
                'self' => 'http://unit.test.org/resource'
            ]
        ];

        /** @var ServerRequestInterface $request */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getParsedBody')
            ->willReturn(json_decode(json_encode($collection)));
        $request->method('getHeader')
            ->with('Content-Type')
            ->willReturn([Document::MEDIA_TYPE]);

        $document = Document::createFromRequest($request, self::$factory);
        $resources = $document->getData();
        $this->assertInstanceOf(Document::class, $document);
        $this->assertContainsOnlyInstancesOf(ResourceObject::class, $resources);
        $this->assertCount(2, $resources);
    }

    public function testDataSingle()
    {
        $resource = new ObjectExample('uuid');
        $relation1 = new RelationExample('rel1');
        $relation2 = new RelationExample('rel2');
        $resource->setRelations([$relation1, $relation2]);
        $document = new Document(self::$factory);
        $document->setData($resource);
        $json = json_decode(json_encode($document), true);
        $this->assertArrayHasKey('data', $json);
        $this->assertArrayHasKey('included', $json);
    }
}
