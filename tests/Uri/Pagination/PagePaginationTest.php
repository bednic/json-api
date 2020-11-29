<?php

declare(strict_types=1);

namespace JSONAPI\Test\URI\Pagination;

use JSONAPI\URI\Pagination\PagePagination;
use PHPUnit\Framework\TestCase;

class PagePaginationTest extends TestCase
{
    public function pageProvider()
    {
        return [
            [['number' => 6, 'size' => 20]],
            [['number' => 1]],
            [['size' => 25]],
            [['number' => 'string', 'size' => null]],
            [[]]
        ];
    }

    /**
     * @dataProvider pageProvider
     */
    public function testParse($data)
    {
        $pagination = new PagePagination();
        $pagination->parse($data);
        $this->assertEquals(26, $pagination->getNumber() + $pagination->getSize());
    }

    /**
     * @depends testConstruct
     */
    public function testGetNumber(PagePagination $pagination)
    {
        $this->assertIsInt($pagination->getNumber());
        $this->assertEquals(1, $pagination->getNumber());
    }

    /**
     * @depends testConstruct
     */
    public function testGetSize(PagePagination $pagination)
    {
        $this->assertIsInt($pagination->getSize());
        $this->assertEquals(25, $pagination->getSize());
    }

    public function testConstruct()
    {
        $pagination = new PagePagination();
        $this->assertInstanceOf(PagePagination::class, $pagination);
        return $pagination;
    }

    /**
     * @depends testConstruct
     */
    public function testSetTotal(PagePagination $pagination)
    {
        $this->expectNotToPerformAssertions();
        $pagination->setTotal(4);
        return $pagination;
    }

    /**
     * @depends testSetTotal
     */
    public function testLast(PagePagination $pagination)
    {
        /** @var PagePagination $pagination */
        $pagination = $pagination->last();
        $this->assertInstanceOf(PagePagination::class, $pagination);
        $this->assertEquals(25, $pagination->getSize());
        $this->assertEquals(4, $pagination->getNumber());
    }

    public function testNext()
    {
        $pagination = new PagePagination(1, 25);
        $pagination->setTotal(4);
        /** @var PagePagination $pagination */
        $pagination = $pagination->next();
        $this->assertInstanceOf(PagePagination::class, $pagination);
        $this->assertEquals(25, $pagination->getSize());
        $this->assertEquals(2, $pagination->getNumber());
        $pagination = $pagination->next();
        $this->assertInstanceOf(PagePagination::class, $pagination);
        $this->assertEquals(25, $pagination->getSize());
        $this->assertEquals(3, $pagination->getNumber());
        $pagination = $pagination->next();
        $this->assertInstanceOf(PagePagination::class, $pagination);
        $this->assertEquals(25, $pagination->getSize());
        $this->assertEquals(4, $pagination->getNumber());
        $pagination = $pagination->next();
        $this->assertNull($pagination);
    }

    public function testPrev()
    {
        $pagination = new PagePagination(4, 25);
        $pagination->setTotal(4);
        $this->assertEquals(25, $pagination->getSize());
        $this->assertEquals(4, $pagination->getNumber());
        /** @var PagePagination $pagination */
        $pagination = $pagination->prev();
        $this->assertInstanceOf(PagePagination::class, $pagination);
        $this->assertEquals(25, $pagination->getSize());
        $this->assertEquals(3, $pagination->getNumber());
        $pagination = $pagination->prev();
        $this->assertInstanceOf(PagePagination::class, $pagination);
        $this->assertEquals(25, $pagination->getSize());
        $this->assertEquals(2, $pagination->getNumber());
        $pagination = $pagination->prev();
        $this->assertInstanceOf(PagePagination::class, $pagination);
        $this->assertEquals(25, $pagination->getSize());
        $this->assertEquals(1, $pagination->getNumber());
        $pagination = $pagination->prev();
        $this->assertNull($pagination);
    }

    /**
     * @depends testSetTotal
     */
    public function testFirst(PagePagination $pagination)
    {
        /** @var PagePagination $pagination */
        $pagination = $pagination->first();
        $this->assertInstanceOf(PagePagination::class, $pagination);
        $this->assertEquals(25, $pagination->getSize());
        $this->assertEquals(1, $pagination->getNumber());
    }

    /**
     * @depends testConstruct
     */
    public function testToString(PagePagination $pagination)
    {
        $this->assertIsString($pagination->__toString());
    }
}
