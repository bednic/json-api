<?php

/**
 * Created by uzivatel
 * at 28.03.2022 11:31
 */

declare(strict_types=1);

namespace JSONAPI\Test\URI\Filtering\OData;

use ExpressionBuilder\Dispatcher\PostgresSQLResolver;
use JSONAPI\Configuration;
use JSONAPI\Driver\SchemaDriver;
use JSONAPI\Metadata\MetadataFactory;
use JSONAPI\Metadata\MetadataRepository;
use JSONAPI\URI\Filtering\OData\ExpressionFilterParser;
use JSONAPI\URI\Filtering\OData\ExpressionFilterResult;
use JSONAPI\URI\Filtering\QData\QuatrodotFilterParser;
use JSONAPI\URI\Path\PathParser;
use JSONAPI\URI\URIParser;
use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\ServerRequestFactory;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Psr16Cache;

class ExpressionFilterParserTest extends TestCase
{
    /**
     * @var MetadataRepository mr
     */
    private static MetadataRepository $mr;
    /**
     * @var string baseURL
     */
    private static string $baseURL;
    /**
     * @var Configuration configuration
     */
    private static Configuration $configuration;

    public static function setUpBeforeClass(): void
    {
        self::$mr      = MetadataFactory::create(
            [RESOURCES . '/valid'],
            new Psr16Cache(new ArrayAdapter()),
            new SchemaDriver()
        );
        self::$baseURL = 'http://unit.test.org';
    }

    public function testParse()
    {
        $filter = "/getter?filter=";
        $filter .= "(contains(stringProperty,'Bonus') or ";
        $filter .= "stringProperty eq 'mortgages') and ";
        $filter .= "boolProperty eq true and ";
        $filter .= "intProperty in (1,2,3) and ";
        $filter .= "dateProperty be (datetime'2015-01-13T02:13:40Z',datetime'2015-01-13T02:13:40Z')";

        $_SERVER["REQUEST_URI"] = $filter;

        $request       = ServerRequestFactory::createFromGlobals();
        $pp            = new PathParser(self::$mr, self::$baseURL);
        $parser        = new ExpressionFilterParser(self::$mr, $pp);
        $configuration = new Configuration(
            self::$mr,
            self::$baseURL,
            625,
            25,
            true,
            true,
            true,
            true,
            null,
            $parser,
            null,
            null,
            $pp
        );
        $up            = (new URIParser($configuration))->parse($request);
        /** @var ExpressionFilterResult $result */
        $result     = $up->getFilter();
        $expression = $result->getCondition();
        $dispatcher = new PostgresSQLResolver();
        $where      = $dispatcher->dispatch($expression);
        $this->assertEquals(
            "((((stringProperty LIKE :0 OR stringProperty = :1) AND boolProperty = :2) AND intProperty IN (:3,:4,:5)) AND (dateProperty BETWEEN :6 AND :7))",
            $where
        );
        $this->assertEquals(
            ["%Bonus%", "mortgages", true, 1, 2, 3, "2015-01-13T02:13:40+00:00", "2015-01-13T02:13:40+00:00"],
            $dispatcher->getParams()
        );
    }
}
