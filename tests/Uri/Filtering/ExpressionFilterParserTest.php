<?php

declare(strict_types=1);

namespace JSONAPI\Test\Uri\Filtering;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Collections\Expr\ClosureExpressionVisitor;
use Doctrine\Common\Collections\Expr\CompositeExpression;
use Doctrine\Common\Collections\Expr\ExpressionVisitor;
use Doctrine\ORM\Persisters\SqlExpressionVisitor;
use Doctrine\ORM\Query\Parameter;
use Doctrine\ORM\Query\QueryExpressionVisitor;
use JSONAPI\Driver\SchemaDriver;
use JSONAPI\Metadata\MetadataFactory;
use JSONAPI\Metadata\MetadataRepository;
use JSONAPI\Test\Resources\Valid\DummyRelation;
use JSONAPI\Test\Resources\Valid\GettersExample;
use JSONAPI\Uri\Filtering\Builder\DoctrineCriteriaExpressionBuilder;
use JSONAPI\Uri\Filtering\Builder\DoctrineQueryExpressionBuilder;
use JSONAPI\Uri\Filtering\ExpressionFilterParser;
use JSONAPI\Uri\UriParser;
use PHPUnit\Framework\TestCase;
use Roave\DoctrineSimpleCache\SimpleCacheAdapter;
use Slim\Psr7\Factory\ServerRequestFactory;

class ExpressionFilterParserTest extends TestCase
{
    /**
     * @var MetadataRepository
     */
    private static MetadataRepository $mr;
    private static string $baseURL;

    public static function setUpBeforeClass(): void
    {
        self::$mr = MetadataFactory::create(
            [RESOURCES . '/valid'],
            new SimpleCacheAdapter(new ArrayCache()),
            new SchemaDriver()
        );
        self::$baseURL = 'http://unit.test.org';
    }

    /**
     * Tests issue with two consecutive single quotes
     *
     * @link https://gitlab.com/bednic/json-api/-/issues/26
     * @link https://gitlab.com/bednic/json-api/-/issues/23
     */
    public function testQuoted()
    {
        $filter = new ExpressionFilterParser(new DoctrineCriteriaExpressionBuilder());
        $text   = "property eq '''va''lue''' and property ne ''''";
        $filter->parse($text);
        /** @var CompositeExpression $condition */
        $condition = $filter->getCondition();
        $visitor   = new QueryExpressionVisitor(['alias']);
        $visitor->dispatch($condition);
        /** @var Parameter[] $params */
        $params = $visitor->getParameters();
        $this->assertEquals("'va'lue'", $params[0]->getValue());
        $this->assertEquals("'", $params[1]->getValue());
    }

    public function testParse()
    {
        $_SERVER["REQUEST_URI"] =
            "/getter?filter=stringProperty eq 'O''Neil' and contains(stringProperty,'asdf') and intProperty in (1,2,3) or boolProperty ne true and relation.property eq null and stringProperty eq datetime'2018-12-01'";
        $request                = ServerRequestFactory::createFromGlobals();
        $up                     = new UriParser($request, self::$mr, self::$baseURL);
        $parser                 = new ExpressionFilterParser(
            new DoctrineQueryExpressionBuilder(
                self::$mr,
                $up->getPath()
            )
        );
        $up->setFilterParser($parser);
        $this->assertEquals(
            "((getter.stringProperty = 'O''Neil' AND getter.stringProperty LIKE '%asdf%') AND getter.intProperty IN(1, 2, 3)) OR ((getter.boolProperty <> true AND relation.property IS NULL) AND getter.stringProperty = '2018-12-01T00:00:00+01:00')",
            (string)$up->getFilter()->getCondition()
        );
        $this->assertArrayHasKey('relation', $up->getFilter()->getRequiredJoins());
        $this->assertEquals(
            'getter.relation',
            $up->getFilter()->getRequiredJoins()['relation'],
        );
    }

    public function testDoctrineCriteriaExpression()
    {
        $url    = "stringProperty eq 'O''Neil' and intProperty in (1,2,3) or boolProperty ne true and stringProperty eq datetime'2018-12-01'";
        $parser = new ExpressionFilterParser(new DoctrineCriteriaExpressionBuilder());
        $parser->parse($url);
        $visitor = new QueryExpressionVisitor(['t']);
        $result  = $visitor->dispatch($parser->getCondition());
        $this->assertEquals(
            "(t.stringProperty = :stringProperty AND t.intProperty IN(:intProperty)) OR (t.boolProperty <> :boolProperty AND t.stringProperty = :stringProperty_3)",
            (string)$result
        );
    }
}
