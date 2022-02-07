<?php

declare(strict_types=1);

namespace JSONAPI\Test\Document;

use JSONAPI\Test\Resources\JSONTestObject;
use PHPUnit\Framework\TestCase;

class ConvertibleTest extends TestCase
{
    public function testJsonSerialize()
    {
        $obj = new JSONTestObject();

        $this->assertEquals(['key' => 'value'], $obj->jsonSerialize());
        $this->assertEquals('{"key":"value"}', json_encode($obj));
    }

    public function testJsonDeserialize()
    {
        $stdTest = json_decode(json_encode(['key' => 'value']), false);
        $arrayTest = json_decode(json_encode(['key' => 'value']), true);

        $obj = JSONTestObject::jsonDeserialize($stdTest);
        $this->assertInstanceOf(JSONTestObject::class, $obj);
        $obj = JSONTestObject::jsonDeserialize($arrayTest);
        $this->assertInstanceOf(JSONTestObject::class, $obj);
    }
}
