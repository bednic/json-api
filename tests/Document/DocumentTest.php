<?php

declare(strict_types=1);

namespace JSONAPI\Test\Document;

use JSONAPI\Document\Document;
use JSONAPI\Document\Error;
use JSONAPI\Document\Id;
use JSONAPI\Document\PrimaryData;
use JSONAPI\Document\ResourceCollection;
use JSONAPI\Document\ResourceObject;
use JSONAPI\Document\ResourceObjectIdentifier;
use JSONAPI\Document\Type;
use JSONAPI\Test\Resources\Valid\GettersExample;
use Opis\JsonSchema\ISchema;
use Opis\JsonSchema\Schema;
use Opis\JsonSchema\Validator;
use PHPUnit\Framework\TestCase;

class DocumentTest extends TestCase
{
    private static ISchema $schema;
    private static Validator $validator;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::$validator = new Validator();
        self::$schema = Schema::fromJsonString(file_get_contents(RESOURCES . '/schema.json'));
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
        $document->addError(new Error());
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
        $this->assertInstanceOf(\JsonSerializable::class, $document);
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
        $result = self::$validator->schemaValidation(json_decode(json_encode($document)), self::$schema);
        return $result->isValid();
    }
}