<?php

/**
 * Created by uzivatel
 * at 23.03.2022 9:57
 */

declare(strict_types=1);

namespace JSONAPI\Test\URI\Filtering\Quatrodot;

use ExpressionBuilder\Dispatcher\PostgreSQLResolver;
use ExpressionBuilder\Expression;
use JSONAPI\Configuration;
use JSONAPI\Data\Collection;
use JSONAPI\Driver\SchemaDriver;
use JSONAPI\Metadata\MetadataFactory;
use JSONAPI\Metadata\MetadataRepository;
use JSONAPI\URI\Filtering\Builder\FieldExpressionBuilder;
use JSONAPI\URI\Filtering\Quatrodot\FilterParser;
use JSONAPI\URI\URIParser;
use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\ServerRequestFactory;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Psr16Cache;

class FilterParserTest extends TestCase
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

    public function testGetCondition()
    {
    }

    public function testParse()
    {
        $_SERVER["REQUEST_URI"] = '/getter?filter=name::contains::Bonus|category::eq::savings|category::eq::mortgages|inserted::gt::2015-01-13T02:13:40Z';
        $request                = ServerRequestFactory::createFromGlobals();
        $parser                 = new FilterParser(new FieldExpressionBuilder());
        $configuration          = new Configuration(
            self::$mr,
            self::$baseURL,
            625,
            25,
            true,
            true,
            true,
            true,
            null,
            $parser
        );
        $up                     = (new URIParser($configuration))->parse($request);
        /** @var Collection<string, Expression> $condition */
        $condition  = $up->getFilter()->getCondition();

        foreach ($condition as $field => $expression) {
            $dispatcher = new PostgreSQLResolver();
            $result = $dispatcher->dispatch($expression);
        }
    }
}
