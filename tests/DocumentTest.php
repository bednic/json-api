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

    /**
     * @depends testConstruct
     */
    public function testAddMeta(Document $document)
    {
        $document->setMeta(new Meta(['count' => 1]));
        $this->expectNotToPerformAssertions();
        return $document;
    }

    /**
     * @depends testAddMeta
     */
    public function testSetData(Document $document)
    {
        $resource = new ObjectExample();
        $relation = new RelationExample();
        $resource->setRelations([$relation]);
        $document->setData($resource);
        print json_encode($document);
        $this->expectOutputRegex('/^((?!\"errors\")(\s|.))*$/');
        $this->expectOutputRegex('/\"data\"/');
        return $document;
    }

    /**
     * @depends testSetData
     */
    public function testGetData(Document $document)
    {
        $this->assertInstanceOf(ResourceObject::class, $document->getData());
    }

    public function testCollection()
    {
        //todo
        $_SERVER["REQUEST_URI"] = "/resource";
        $document = new Document(self::$factory);
        $collection = [];
        $collection[] = new ObjectExample('id1');
        $collection[] = new ObjectExample('id2');

        $document->setData($collection);
        $this->assertIsArray($document->getData());
        $this->assertCount(2, $document->getData());
    }

    /**
     * @depends testSetData
     */
    public function testJsonSerialize(Document $document)
    {
        $this->assertEquals(trim(file_get_contents(__DIR__ . '/resources/response.json')), json_encode($document));
    }

    /**
     * @depends testSetData
     */
    public function testToString(Document $document)
    {
        $this->assertEquals((string)$document, json_encode($document));
    }

    /**
     * @depends testConstruct
     */
    public function testAddLink(Document $document)
    {
        $document->addLink(new Link('own', 'http://my-own.link.com'));
        $document->setLinks([
            new Link('link1', 'http://link1.com')
        ]);
        $this->expectNotToPerformAssertions();
    }

    /**
     * @depends testConstruct
     */
    public function testAddError(Document $document)
    {
        try {
            throw new BadRequest("Test exception");
        } catch (BadRequest $exception) {
            $error = Error::fromException($exception);
            $document->addError($error);
            print json_encode($document);
            $this->expectOutputRegex('/^((?!\"data\")(\s|.))*$/');
            $this->expectOutputRegex('/\"errors\"/');
        }
    }

    public function testCreateFromRequest()
    {
        /** @var ServerRequestInterface $request */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getParsedBody')
            ->willReturn(json_decode(trim(file_get_contents(__DIR__ . '/resources/request.json'))));
        $request->method('getHeader')
            ->with('Content-Type')
            ->willReturn([Document::MEDIA_TYPE]);

        $document = Document::createFromRequest($request, self::$factory);
        $this->assertInstanceOf(Document::class, $document);
        $this->assertInstanceOf(ResourceObject::class, $document->getData());
    }
}
