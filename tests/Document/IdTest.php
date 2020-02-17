<?php

declare(strict_types=1);

namespace JSONAPI\Test\Document;

use JSONAPI\Document\Id;
use PHPUnit\Framework\TestCase;

class IdTest extends TestCase
{

    public function testSetData()
    {
        $id = new Id('id');
        $this->assertIsString($id->getData());
        $this->assertEquals('id', $id->getData());
        $id->setData(null);
        $this->assertNull($id->getData());
    }

    public function testConstruct()
    {
        $id = new Id('id');
        $this->assertInstanceOf(Id::class, $id);
        $id = new Id(null);
        $this->assertInstanceOf(Id::class, $id);
    }
}
