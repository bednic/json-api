<?php

namespace JSONAPI\Test;

use JSONAPI\Document\Attribute;
use JSONAPI\Document\Link;
use JSONAPI\Document\Meta;
use JSONAPI\Exception\Document\ForbiddenCharacter;
use JSONAPI\Exception\Document\ForbiddenDataType;
use JSONAPI\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class FieldTest extends TestCase
{
    public function testAttribute()
    {
        $attribute = new Attribute('key', 'value');
        $this->assertInstanceOf(Attribute::class, $attribute);
        $this->assertEquals('key', $attribute->getKey());
        $this->assertEquals('value', $attribute->getData());
        $this->assertEquals('value', $attribute->jsonSerialize());
    }

    public function testLink()
    {
        $meta = new Meta(['key' => 'value']);
        $link = new Link('uri', 'http://www.google.com');
        $this->assertEquals('uri', $link->getKey());
        $this->assertEquals('http://www.google.com', $link->getData());
        $link->setMeta($meta);
        $this->assertIsArray($link->getData());
        $this->assertArrayHasKey('href', $link->jsonSerialize());
        $this->assertArrayHasKey('meta', $link->jsonSerialize());
    }

    public function testForbiddenCharacter()
    {
        $this->expectException(ForbiddenCharacter::class);
        new Attribute('key*', 'value');
    }

    public function testBadUrl()
    {
        $this->expectException(InvalidArgumentException::class);
        new Link('uri', 'some-nonsence');
    }

    public function testBadData()
    {
        $this->expectException(ForbiddenDataType::class);
        $resource = fopen(__DIR__ . '/resources/forbidden_data_type', 'w+');
        new Attribute('key', $resource);
    }

    protected function tearDown(): void
    {
        if (file_exists(__DIR__ . '/resources/forbidden_data_type')) {
            unlink(__DIR__ . '/resources/forbidden_data_type');
        }
    }
}
