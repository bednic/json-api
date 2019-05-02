<?php

namespace Test\JSONAPI;

use JSONAPI\Filter\Condition;
use JSONAPI\Filter\URL;
use PHPUnit\Framework\TestCase;
use function Sodium\crypto_box_publickey_from_secretkey;

class URLTest extends TestCase
{

    public function test__construct()
    {
        $url = new URL();
        $this->assertInstanceOf(URL::class, $url);
        return $url;
    }

    /**
     * @depends test__construct
     */
    public function testGetIncludes(URL $url)
    {
        $includes = $url->getIncludes();
        $this->assertIsArray($includes);
        $this->assertArrayHasKey('relations', $includes);
    }

    /**
     * @param URL $url
     * @depends test__construct
     */
    public function testGetFieldsFor(URL $url)
    {
        $fields = $url->getFieldsFor('resource');
        $this->assertIsArray($fields);
        $this->assertContains('publicProperty', $fields);
        $this->assertContains('privateProperty', $fields);
        $this->assertContains('relations', $fields);
    }

    /**
     * @param URL $url
     * @depends test__construct
     */
    public function testGetSort(URL $url)
    {
        $sort = $url->getSort();
        $this->assertArrayHasKey('publicProperty', $sort);
        $this->assertArrayHasKey('privateProperty', $sort);
        $this->assertEquals('DESC', $sort['publicProperty']);
        $this->assertEquals('ASC', $sort['privateProperty']);
    }

    /**
     * @param URL $url
     * @depends test__construct
     */
    public function testGetFilter(URL $url)
    {
        $filter = $url->getFilter();
        $this->assertArrayHasKey('publicProperty', $filter);
        /** @var Condition $condition */
        foreach ($filter['publicProperty'] as $condition) {
            $this->assertContains($condition->operand, [URL::EQUAL, URL::NOT_EQUAL, URL::GREATER_THEN, URL::LOWER_THEN, URL::LIKE, URL::IN]);
            if ($condition->operand === URL::IN) {
                $this->assertIsArray($condition->value);
            }
        }
        $this->assertIsArray($filter);
    }

    /**
     * @param URL $url
     * @depends test__construct
     */
    public function testGetPagination(URL $url)
    {
        $pagination = $url->getPagination();
        $this->assertIsArray($pagination);
        $this->assertArrayHasKey(URL::OFFSET, $pagination);
        $this->assertArrayHasKey(URL::LIMIT, $pagination);
        $this->assertEquals(10, $pagination[URL::OFFSET]);
        $this->assertEquals(20, $pagination[URL::LIMIT]);
    }
}
