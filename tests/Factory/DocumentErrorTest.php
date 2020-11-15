<?php

declare(strict_types=1);

namespace JSONAPI\Test\Factory;

use JSONAPI\Document\Error;
use JSONAPI\Exception\InvalidArgumentException;
use JSONAPI\Factory\DocumentErrorFactory;
use PHPUnit\Framework\TestCase;
use Swaggest\JsonSchema\InvalidValue;

class DocumentErrorTest extends TestCase
{

    public function testConstruct()
    {
        $instance = new DocumentErrorFactory();
        $this->assertInstanceOf(DocumentErrorFactory::class, $instance);
    }

    public function testFromThrowable()
    {
        $i = new DocumentErrorFactory();
        $error = $i->fromThrowable(new \Exception("Unknown exception"));
        $this->assertInstanceOf(Error::class, $error);
        $jae = $i->fromThrowable(new InvalidArgumentException());
        $this->assertInstanceOf(Error::class, $jae);
        $se = $i->fromThrowable(new InvalidValue());
        $this->assertInstanceOf(Error::class, $se);
    }
}
