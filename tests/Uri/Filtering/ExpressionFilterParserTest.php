<?php

declare(strict_types=1);

namespace JSONAPI\Test\URI\Filtering;

use DateTime;
use Doctrine\Common\Collections\Expr\CompositeExpression;
use Doctrine\ORM\Query\Parameter;
use Doctrine\ORM\Query\QueryExpressionVisitor;
use ExpressionBuilder\Dispatcher\ClosureResolver;
use JSONAPI\Driver\SchemaDriver;
use JSONAPI\Factory\MetadataFactory;
use JSONAPI\Metadata\MetadataRepository;
use JSONAPI\URI\Filtering\Builder\ClosureExpressionBuilder;
use JSONAPI\URI\Filtering\Builder\DoctrineCriteriaExpressionBuilder;
use JSONAPI\URI\Filtering\Builder\DoctrineQueryExpressionBuilder;
use JSONAPI\URI\Filtering\ExpressionFilterParser;
use JSONAPI\URI\URIParser;
use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\ServerRequestFactory;
use stdClass;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Psr16Cache;

class ExpressionFilterParserTest extends TestCase
{
    /**
     * @var MetadataRepository
     */
    private static MetadataRepository $mr;
    private static string $baseURL;

    public static function setUpBeforeClass(): void
    {
        self::$mr      = MetadataFactory::create(
            [RESOURCES . '/valid'],
            new Psr16Cache(new ArrayAdapter()),
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
        $up                     = new URIParser($request, self::$mr, self::$baseURL);
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

    public function testClosureExpressionBuilderUsage()
    {
        $std                 = new stdClass();
        $std->stringProperty = "O'Neil";
        $std->intProperty    = 2;
        $std->boolProperty   = true;
        $std->dateProperty   = new DateTime('2020-12-01');
        $data                = [$std];
        $url                 =
            "stringProperty eq 'O''Neil'" .
            " and " .
            "intProperty in (1,2,3)" .
            " or " .
            "boolProperty ne true" .
            " and " .
            "dateProperty eq datetime'2020-12-01'";
        $parser              = new ExpressionFilterParser(new ClosureExpressionBuilder());
        $parser->parse($url);
        $visitor = new ClosureResolver();
        $filter  = $visitor->dispatch($parser->getCondition());
        $result  = array_filter($data, $filter);
        $this->assertIsCallable($filter);
        $this->assertContains($std, $result);
    }

    public function testIssue37()
    {
        $obj1             = new stdClass();
        $obj1->collection = [1, 2, 3];
        $obj2             = new stdClass();
        $obj2->collection = [2, 3, 4];
        $data             = [$obj1, $obj2];
        $example          = "not collection has 1";
        $parser           = new ExpressionFilterParser();
        $parser->parse($example);
        $visitor = new ClosureResolver();
        $filter  = $visitor->dispatch($parser->getCondition());
        $result  = array_filter($data, $filter);
        $this->assertContains($obj2, $result);
    }

    public function testIssue38()
    {
        $obj1       = new stdClass();
        $obj1->name = "Foo";
        $obj2       = new stdClass();
        $obj2->name = "Bar";
        $data       = [$obj1, $obj2];
        $example    = "tolower(name) eq 'foo'";
        $parser     = new ExpressionFilterParser();
        $parser->parse($example);
        $visitor = new ClosureResolver();
        $filter  = $visitor->dispatch($parser->getCondition());
        $result  = array_filter($data, $filter);
        $this->assertContains($obj1, $result);
    }
}
