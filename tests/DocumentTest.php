<?php

namespace Test\JSONAPI;

use Doctrine\Common\Collections\ArrayCollection;
use JSONAPI\Document\Document;
use JSONAPI\Document\Error;
use JSONAPI\Document\Resource;
use JSONAPI\Encoder;
use JSONAPI\EncoderOptions;
use JSONAPI\Exception\DocumentException;
use JSONAPI\LinkProvider;
use JSONAPI\MetadataFactory;
use PHPUnit\Framework\TestCase;

class DocumentTest extends TestCase
{
    /**
     * @var Document
     */
    private $document;

    public function testCreate(): Document
    {
        $object = new ObjectExample();
        $relation = new RelationExample();
        $object->setRelations([$relation]);
        $encoder = new Encoder(new MetadataFactory(__DIR__ . '/resources'));
        $data = $encoder->encode($object, new EncoderOptions(true));
        $includes = [$encoder->encode($relation, new EncoderOptions(true))];
        $links = ['own' => 'http://my-own.link.com'];
        $meta = [
            'count' => 1
        ];
        $document = Document::create($data, $includes, $links, $meta);
        $this->assertInstanceOf(Document::class, $document);


        return $document;
    }

    /**
     * @depends testCreate
     */
    public function testGetData(Document $document)
    {
        $this->assertInstanceOf(Resource::class, $document->getData());
    }

    /**
     * @depends testCreate
     */
    public function testJsonSerialize(Document $document)
    {
        $this->assertEquals(file_get_contents(__DIR__ . '/resources/response.json'), json_encode($document));
    }

    /**
     * @depends testCreate
     */
    public function test__toString(Document $document)
    {
        $this->assertEquals((string)$document, json_encode($document));
    }

    /**
     * @depends testCreate
     */
    public function testGetLink(Document $document)
    {
        $this->assertNotEmpty($document->getLink(LinkProvider::SELF));
        $this->assertEquals("http://my-own.link.com", $document->getLink('own'));
    }

    /**
     * @depends testCreate
     */
    public function testGetMeta(Document $document)
    {
        $this->assertEquals(1, $document->getMeta('count'));
    }

    /**
     * @depends testCreate
     */
    public function testGetIncludes(Document $document)
    {
        $this->assertInstanceOf(ArrayCollection::class, $document->getIncludes());
        $this->assertTrue($document->getIncludes()->count() > 0);
    }

    protected function setUp(): void
    {
        $this->document = new Document();
    }

    public function test__construct()
    {
        $document = new Document();
        $this->assertInstanceOf(Document::class, $document);
        return $document;
    }

    public function testAddLink()
    {
        $this->document->addLink('own', 'http://my-own.link.com');
        $this->assertEquals('http://my-own.link.com', $this->document->getLink('own'));
    }


    public function testAddError()
    {
        try {
            throw new DocumentException("Test exception");
        } catch (DocumentException $exception) {
            $error = new Error($exception);
            $this->document->addError($error);
            print json_encode($this->document);
            $this->expectOutputRegex('/^((?!\"data\")(\s|.))*$/');
            $this->expectOutputRegex('/\"errors\"/');
        }
    }

    public function testSetData()
    {
        $data = [];
        $this->document->setData($data);
        print json_encode($this->document);
        $this->expectOutputRegex('/^((?!\"errors\")(\s|.))*$/');
        $this->expectOutputRegex('/\"data\"/');
    }


    public function testAddMeta()
    {
        $this->document->addMeta('author', 'me');
        $this->assertEquals('me', $this->document->getMeta('author'));
    }
}
