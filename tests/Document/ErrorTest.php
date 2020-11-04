<?php

declare(strict_types=1);

namespace JSONAPI\Test\Document;

use JSONAPI\Document\Error;
use JSONAPI\Exception\Document\AttributeNotExist;
use JSONAPI\Exception\Http\UnsupportedParameter;
use JSONAPI\Exception\Metadata\AttributeNotFound;
use PHPUnit\Framework\MockObject\MockBuilder;
use PHPUnit\Framework\MockObject\MockClass;
use PHPUnit\Framework\TestCase;
use Swaggest\JsonSchema\InvalidValue;

class ErrorTest extends TestCase
{
    public function testFromException()
    {
        $e = json_decode(
            json_encode(Error::fromException(new \Exception("Test Generic Exception Message"))->jsonSerialize()),
            true
        );
        $this->assertArrayHasKey('line', $e['source']);
        $this->assertArrayHasKey('trace', $e['source']);
        $ee = json_decode(json_encode(Error::fromException(new UnsupportedParameter('sort'))->jsonSerialize()), true);
        $this->assertArrayHasKey('parameter', $ee['source']);
        $eee = json_decode(
            json_encode(Error::fromException(new AttributeNotExist('testAttribute'))->jsonSerialize()),
            true
        );
        $this->assertArrayHasKey('pointer', $eee['source']);
        $eeee = json_decode(
            json_encode(Error::fromException(new InvalidValue("test"))),
            true
        );
        $this->assertArrayHasKey('pointer', $eeee['source']);
    }
}
