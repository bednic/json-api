<?php

declare(strict_types=1);

namespace JSONAPI\Test\Uri\Filtering;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\ORM\Persisters\SqlExpressionVisitor;
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

    public static function setUpBeforeClass(): void
    {
        self::$mr = MetadataFactory::create(
            [RESOURCES . '/valid'],
            new SimpleCacheAdapter(new ArrayCache()),
            new SchemaDriver()
        );
    }

    public function testParse()
    {
        $_SERVER["REQUEST_URI"] = "/getter?filter=stringProperty eq 'string' and intProperty in (1,2,3) or boolProperty neq true and relation.property eq null";
        $request = ServerRequestFactory::createFromGlobals();
        $up = new UriParser($request, self::$mr);
        $parser = new ExpressionFilterParser(new DoctrineQueryExpressionBuilder(self::$mr, $up->getPath()));
        $up->setFilterParser($parser);
        $this->assertEquals(
            "(getter.stringProperty = string AND getter.intProperty IN(1, 2, 3)) OR (getter.boolProperty <> 1 AND relation.property IS NULL)",
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
        $url = "stringProperty eq '3' and intProperty in (1,2,3) or boolProperty neq true";
        $parser = new ExpressionFilterParser(new DoctrineCriteriaExpressionBuilder());
        $parser->parse($url);
        $visitor = new QueryExpressionVisitor(['t']);
        $result = $visitor->dispatch($parser->getCondition());
        $this->assertEquals(
            "(t.stringProperty = :stringProperty AND t.intProperty IN(:intProperty)) OR t.boolProperty <> :boolProperty",
            (string)$result
        );
    }
}
