<?php

declare(strict_types=1);

namespace JSONAPI\Test\Document;

use JSONAPI\Document\Document;
use JSONAPI\Document\Error;
use JSONAPI\Document\Id;
use JSONAPI\Document\Meta;
use JSONAPI\Document\PrimaryData;
use JSONAPI\Document\ResourceCollection;
use JSONAPI\Document\ResourceObject;
use JSONAPI\Document\Type;
use JsonSerializable;
use PHPUnit\Framework\TestCase;
use Swaggest\JsonSchema\Schema;
use Swaggest\JsonSchema\SchemaContract;

class DocumentTest extends TestCase
{
    private static SchemaContract $schema;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::$schema = Schema::import(json_decode(file_get_contents(__DIR__ . '/../../src/Middleware/out.json')));
    }

    public function testToString()
    {
        $document = new Document();
        $this->assertIsString($document->__toString());
    }

    public function testSetIncludes()
    {
        $document = new Document();
        $included = new ResourceCollection();
        $included->add(new ResourceObject(new Type('test'), new Id('1')));
        $document->setIncludes($included);
        $this->assertTrue($this->isValid($document));
    }

    public function testGetData()
    {
        $document = new Document();
        $this->assertNull($document->getData());
        $document->setData(new ResourceCollection());
        $this->assertInstanceOf(PrimaryData::class, $document->getData());
        $this->assertTrue($this->isValid($document));
    }

    public function testJsonSerialize()
    {
        $document = new Document();
        $this->assertInstanceOf(JsonSerializable::class, $document);
        $this->assertIsString(json_encode($document));
    }

    public function testConstruct()
    {
        $document = new Document();

        $this->assertInstanceOf(Document::class, $document);
        $this->assertTrue($this->isValid($document));
    }

    public function testSetData()
    {
        $document = new Document();
        $data = new ResourceCollection();
        $document->setData($data);
        $this->assertTrue($this->isValid($document));
    }

    public function testAddError()
    {
        $document = new Document();
        $error = new Error();
        $document->addError($error);
        $this->assertTrue($this->isValid($document));
    }

    private function isValid(Document $document): bool
    {
        self::$schema->in(json_decode(json_encode($document)));
        return true;
    }

    public function testSetJSONAPIObjectMeta()
    {
        $document = new Document();
        $document->setJSONAPIObjectMeta(new Meta(['prop' => 'value']));
        $document->setMeta(new Meta(['prop' => 'value']));
        $json = $document->jsonSerialize();
        $this->assertObjectHasAttribute('meta', $json->jsonapi);
    }
}
