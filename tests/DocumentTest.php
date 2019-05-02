<?php

namespace Test\JSONAPI;

use Doctrine\Common\Collections\ArrayCollection;
use JSONAPI\Document\Document;
use JSONAPI\Document\Error;
use JSONAPI\Document\Link;
use JSONAPI\Document\Meta;
use JSONAPI\Document\Resource;
use JSONAPI\Encoder;
use JSONAPI\EncoderOptions;
use JSONAPI\Exception\DocumentException;
use JSONAPI\LinkProvider;
use JSONAPI\MetadataFactory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

class DocumentTest extends TestCase
{
    
    public function test__construct()
    {
        $document = new Document(
            new MetadataFactory(__DIR__.'/resources')
        );
        $this->assertInstanceOf(Document::class, $document);
        return $document;
    }

    /**
     * @param Document $document
     * @depends test__construct
     * @return Document
     */
    public function testAddMeta(Document $document)
    {
        $document->addMeta(new Meta('count',1));
        $this->expectNotToPerformAssertions();
        return $document;
    }

    /**
     * @param Document $document
     * @depends testAddMeta
     * @return Document
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
        $this->assertInstanceOf(Resource::class, $document->getData());
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
    public function test__toString(Document $document)
    {
        $this->assertEquals((string)$document, json_encode($document));
    }

    /**
     * @param Document $document
     * @depends test__construct
     */
    public function testAddLink(Document $document)
    {
        $document->addLink(new Link('own', 'http://my-own.link.com'));
        $this->expectNotToPerformAssertions();
    }

    /**
     * @param Document $document
     * @depends test__construct
     */
    public function testAddError(Document $document)
    {
        try {
            throw new DocumentException("Test exception");
        } catch (DocumentException $exception) {
            $error = Error::fromException($exception);
            $document->addError($error);
            print json_encode($document);
            $this->expectOutputRegex('/^((?!\"data\")(\s|.))*$/');
            $this->expectOutputRegex('/\"errors\"/');
        }
    }

    public function testCreateFromRequest()
    {
        /** @var RequestInterface $request */
        $request = $this->createMock(RequestInterface::class);
        $request->method('getBody')
            ->willReturn(trim(file_get_contents(__DIR__.'/resources/request.json')));
        $request->method('getHeader')
            ->with('Content-Type')
            ->willReturn([Document::MEDIA_TYPE]);

        $document = Document::createFromRequest($request,new MetadataFactory(__DIR__.'/resources'));
        $this->assertInstanceOf(Document::class,$document);
        $this->assertInstanceOf(Resource::class, $document->getData());

    }


}

