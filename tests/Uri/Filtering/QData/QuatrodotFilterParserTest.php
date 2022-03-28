<?php

/**
 * Created by uzivatel
 * at 23.03.2022 9:57
 */

declare(strict_types=1);

namespace JSONAPI\Test\URI\Filtering\QData;

use ExpressionBuilder\Dispatcher\PostgresSQLResolver;
use JSONAPI\Configuration;
use JSONAPI\Driver\SchemaDriver;
use JSONAPI\Metadata\MetadataFactory;
use JSONAPI\Metadata\MetadataRepository;
use JSONAPI\URI\Filtering\QData\QuatrodotFilterParser;
use JSONAPI\URI\Filtering\QData\QuatrodotResult;
use JSONAPI\URI\Path\PathParser;
use JSONAPI\URI\URIParser;
use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\ServerRequestFactory;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Psr16Cache;

class QuatrodotFilterParserTest extends TestCase
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
        $filter .= "stringProperty::contains::Bonus|";
        $filter .= "boolProperty::eq::true|";
        $filter .= "intProperty::in::1::2::3|";
        $filter .= "stringProperty::eq::mortgages|";
        $filter .= "dateProperty::be::2015-01-13T02:13:40Z::2015-01-13T02:13:40Z";

        $_SERVER["REQUEST_URI"] = $filter;

        $request       = ServerRequestFactory::createFromGlobals();
        $pp            = new PathParser(self::$mr, self::$baseURL);
        $parser        = new QuatrodotFilterParser(self::$mr, $pp);
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
        /** @var QuatrodotResult $result */
        $result     = $up->getFilter();
        $expression = $result->getCondition();
        $dispatcher = new PostgresSQLResolver();
        $where      = $dispatcher->dispatch($expression);
        $params     = $dispatcher->getParams();

        $this->assertEquals(
            "((((stringProperty LIKE :0 OR stringProperty = :1) AND boolProperty = :2) AND intProperty IN (:3,:4,:5)) AND (dateProperty BETWEEN :6 AND :7))",
            $where
        );
        $this->assertEquals(
            ["%Bonus%", "mortgages", true, 1, 2, 3, "2015-01-13T02:13:40+00:00", "2015-01-13T02:13:40+00:00"],
            $params
        );
        $dispatcher  = new PostgresSQLResolver();
        $stringEx    = $result->getPartialCondition("stringProperty");
        $stringWhere = $dispatcher->dispatch($stringEx);
        $this->assertEquals("(stringProperty LIKE :0 OR stringProperty = :1)", $stringWhere);
        $this->assertEquals(["%Bonus%", "mortgages"], $dispatcher->getParams());
    }
}
