<?php

declare(strict_types=1);

namespace JSONAPI\Test\Uri\Pagination;

use JSONAPI\Uri\Pagination\LimitOffsetPagination;
use JSONAPI\Uri\Pagination\PaginationInterface;
use PHPUnit\Framework\TestCase;

class LimitOffsetPaginationTest extends TestCase
{
    public function parseProvider()
    {
        return [
            [['limit' => 25, 'offset' => 0]],
            [['limit' => 25]],
            [['offset' => 0]],
            [['limit' => 'string', 'offset' => null]],
            [[]]
        ];
    }

    public function totalProvider()
    {
        return [
            [100],
            ["string"],
            [null],
            [''],
            [1321.21132]
        ];
    }

    public function testGetLimit()
    {
        $lop = new LimitOffsetPagination();
        $this->assertIsInt($lop->getLimit());
        $this->assertEquals(25, $lop->getLimit());
    }

    public function testPrev()
    {
        $lop = new LimitOffsetPagination();
        $this->assertNull($lop->prev());
        $lop = new LimitOffsetPagination(50, 25);
        $lop->setTotal(100);
        $prev = $lop->prev();
        $this->assertInstanceOf(LimitOffsetPagination::class, $prev);
        $this->assertEquals(25, $prev->getLimit());
        $this->assertEquals(25, $prev->getOffset());
    }


    /**
     * @dataProvider totalProvider
     */
    public function testSetTotal($total)
    {
        $lop = new LimitOffsetPagination();
        if ($total === 100) {
            $this->expectNotToPerformAssertions();
        } else {
            $this->expectException(\TypeError::class);
        }
        $lop->setTotal($total);
    }

    /**
     * @dataProvider parseProvider
     */
    public function testParse($data)
    {
        $parser = new LimitOffsetPagination();
        $parser->parse($data);
        $this->assertEquals(25, $parser->getLimit());
        $this->assertEquals(0, $parser->getOffset());
    }

    public function testConstruct()
    {
        $this->assertInstanceOf(LimitOffsetPagination::class, new LimitOffsetPagination());
        $this->assertInstanceOf(LimitOffsetPagination::class, new LimitOffsetPagination(100, 200));
        $this->expectException(\TypeError::class);
        $this->assertInstanceOf(LimitOffsetPagination::class, new LimitOffsetPagination('stirng', 1.23));
    }

    public function testGetOffset()
    {
        $pagination = new LimitOffsetPagination(10);
        $this->assertEquals(10, $pagination->getOffset());
    }

    public function testToString()
    {
        $pagination = new LimitOffsetPagination();
        $this->assertIsString($pagination->__toString());
    }

    public function testNext()
    {
        $pagination = new LimitOffsetPagination();
        $this->assertInstanceOf(PaginationInterface::class, $pagination->next());
    }

    public function testFirst()
    {
        $pagination = new LimitOffsetPagination();
        $this->assertInstanceOf(PaginationInterface::class, $pagination->first());
    }

    public function testLast()
    {
        $pagination = new LimitOffsetPagination();
        $pagination->setTotal(100);
        $this->assertInstanceOf(PaginationInterface::class, $pagination->last());
    }
}
