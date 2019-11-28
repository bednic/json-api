<?php

namespace JSONAPI\Test;

use Grpc\Server;
use JSONAPI\Uri\Filter;
use JSONAPI\Uri\Pagination;
use JSONAPI\Uri\Query;
use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\RequestFactory;
use Slim\Psr7\Factory\ServerRequestFactory;

/**
 * Class URLTest
 *
 * @package JSONAPI\Test
 */
class URLTest extends TestCase
{

    public function testConstruct()
    {
        $request = ServerRequestFactory::createFromGlobals();
        $query = new Query($request);
        $this->assertInstanceOf(Query::class, $query);
        return $query;
    }

    /**
     * @depends testConstruct
     */
    public function testGetIncludes(Query $url)
    {
        $includes = $url->getIncludes();
        $this->assertIsArray($includes);
        $this->assertArrayHasKey('relations', $includes);
    }

    /**
     * @depends testConstruct
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
     * @depends testConstruct
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
     * @depends testConstruct
     */
    public function testGetFilter(Query $url)
    {
        $filter = $url->getFilter();
        $this->assertInstanceOf(Filter::class, $filter);
        $this->assertNotNull($filter->getCondition());
        $this->assertEquals('attribute eq \'value\'', $filter->getCondition());
    }

    /**
     * @depends testConstruct
     */
    public function testGetPagination(Query $url)
    {
        $pagination = $url->getPagination();
        $this->assertInstanceOf(Pagination::class, $pagination);
        $this->assertEquals(10, $pagination->getOffset());
        $this->assertEquals(20, $pagination->getLimit());
    }
}
