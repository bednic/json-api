<?php

declare(strict_types=1);

namespace JSONAPI\Test\Uri\Filtering;

use Doctrine\ORM\Query\QueryExpressionVisitor;
use JSONAPI\Uri\Filtering\Builder\DoctrineCriteriaExpressionBuilder;
use JSONAPI\Uri\Filtering\Builder\DoctrineQueryExpressionBuilder;
use JSONAPI\Uri\Filtering\ExpressionFilterParser;
use PHPUnit\Framework\TestCase;

class ExpressionFilterParserTest extends TestCase
{
    public function testParse()
    {
        $url = "key eq 3 and key2 in (1,2,3) or key3 neq null";
        $parser = new ExpressionFilterParser(new DoctrineQueryExpressionBuilder());
        $parser->parse($url);
        $this->assertEquals("(key = 3 AND key2 IN(1, 2, 3)) OR key3 IS NOT NULL", (string)$parser->getCondition());
        $parser = new ExpressionFilterParser(new DoctrineCriteriaExpressionBuilder());
        $parser->parse($url);
        $visitor = new QueryExpressionVisitor(['test']);
        $result = $visitor->dispatch($parser->getCondition());
        $this->assertEquals("(test.key = :key AND test.key2 IN(:key2)) OR test.key3 IS NOT NULL", (string)$result);
    }
}
