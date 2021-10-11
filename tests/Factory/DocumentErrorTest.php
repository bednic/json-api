<?php

declare(strict_types=1);

namespace JSONAPI\Test\Factory;

use JSONAPI\Document\Error;
use JSONAPI\Document\Error\DefaultErrorFactory;
use JSONAPI\Document\Error\ErrorFactory;
use JSONAPI\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Swaggest\JsonSchema\InvalidValue;

class DocumentErrorTest extends TestCase
{

    public function testConstruct()
    {
        $instance = new DefaultErrorFactory();
        $this->assertInstanceOf(ErrorFactory::class, $instance);
    }

    public function testFromThrowable()
    {
        $i = new DefaultErrorFactory();
        $error = $i->fromThrowable(new \Exception("Unknown exception"));
        $this->assertInstanceOf(Error::class, $error);
        $jae = $i->fromThrowable(new InvalidArgumentException());
        $this->assertInstanceOf(Error::class, $jae);
        $se = $i->fromThrowable(new InvalidValue());
        $this->assertInstanceOf(Error::class, $se);
    }
}
