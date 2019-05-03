<?php

namespace Test\JSONAPI;

use JSONAPI\Query\Condition;
use JSONAPI\Query\Query;
use PHPUnit\Framework\TestCase;
use function Sodium\crypto_box_publickey_from_secretkey;

class URLTest extends TestCase
{

    public function test__construct()
    {
        $url = new Query();
        $this->assertInstanceOf(Query::class, $url);
        return $url;
    }

    /**
     * @depends test__construct
     */
    public function testGetIncludes(Query $url)
    {
        $includes = $url->getIncludes();
        $this->assertIsArray($includes);
        $this->assertArrayHasKey('relations', $includes);
    }

    /**
     * @param Query $url
     * @depends test__construct
     */
    public function testGetFieldsFor(Query $url)
    {
        $fields = $url->getFieldsFor('resource');
        $this->assertIsArray($fields);
        $this->assertContains('publicProperty', $fields);
        $this->assertContains('privateProperty', $fields);
        $this->assertContains('relations', $fields);
    }

    /**
     * @param Query $url
     * @depends test__construct
     */
    public function testGetSort(Query $url)
    {
        $sort = $url->getSort();
        $this->assertArrayHasKey('publicProperty', $sort);
        $this->assertArrayHasKey('privateProperty', $sort);
        $this->assertEquals('DESC', $sort['publicProperty']);
        $this->assertEquals('ASC', $sort['privateProperty']);
    }

    /**
     * @param Query $url
     * @depends test__construct
     */
    public function testGetFilter(Query $url)
    {
        $filter = $url->getFilter();
        $this->assertArrayHasKey('publicProperty', $filter);
        /** @var Condition $condition */
        foreach ($filter['publicProperty'] as $condition) {
            $this->assertContains($condition->operand, [Query::EQUAL, Query::NOT_EQUAL, Query::GREATER_THEN, Query::LOWER_THEN, Query::LIKE, Query::IN]);
            if ($condition->operand === Query::IN) {
                $this->assertIsArray($condition->value);
            }
        }
        $this->assertIsArray($filter);
    }

    /**
     * @param Query $url
     * @depends test__construct
     */
    public function testGetPagination(Query $url)
    {
        $pagination = $url->getPagination();
        $this->assertIsArray($pagination);
        $this->assertArrayHasKey(Query::OFFSET, $pagination);
        $this->assertArrayHasKey(Query::LIMIT, $pagination);
        $this->assertEquals(10, $pagination[Query::OFFSET]);
        $this->assertEquals(20, $pagination[Query::LIMIT]);
    }
}
