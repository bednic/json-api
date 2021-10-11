<?php

/**
 * Created by lasicka@logio.cz
 * at 11.10.2021 10:48
 */

declare(strict_types=1);

namespace JSONAPI\Test;

use JSONAPI\Configuration;
use JSONAPI\Driver\SchemaDriver;
use JSONAPI\Exception\InvalidConfigurationParameter;
use JSONAPI\Metadata\MetadataFactory;
use JSONAPI\Metadata\MetadataRepository;
use JSONAPI\URI\Fieldset\FieldsetParser;
use JSONAPI\URI\Filtering\ExpressionFilterParser;
use JSONAPI\URI\Inclusion\InclusionParser;
use JSONAPI\URI\Pagination\LimitOffsetPagination;
use JSONAPI\URI\Path\PathParser;
use JSONAPI\URI\Sorting\SortParser;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Psr16Cache;

class ConfigurationTest extends TestCase
{
    /**
     * @var MetadataRepository mr
     */
    private static MetadataRepository $mr;
    /**
     * @var string url
     */
    private static string $url;

    public static function setupBeforeClass(): void
    {
        self::$mr  = MetadataFactory::create(
            [RESOURCES . '/valid'],
            new Psr16Cache(new ArrayAdapter()),
            new SchemaDriver()
        );
        self::$url = 'http://unit.test.org';
    }


    public function testConstruct()
    {
        $min  = new Configuration(self::$mr, self::$url);
        $full = new Configuration(
            self::$mr,
            self::$url,
            0,
            0,
            false,
            false,
            false,
            false,
            new FieldsetParser(),
            new ExpressionFilterParser(),
            new InclusionParser(),
            new LimitOffsetPagination(),
            new PathParser(self::$mr, self::$url),
            new SortParser(),
            new NullLogger()
        );
        $this->assertInstanceOf(Configuration::class, $min);
        $this->assertInstanceOf(Configuration::class, $full);
    }

    public function badUrlProvider()
    {
        return [
            ['unit.test.org'],
            [''],
            ['unit@test.com']
        ];
    }

    /**
     * @dataProvider badUrlProvider
     */
    public function testInvalidURL($url)
    {
        $this->expectException(InvalidConfigurationParameter::class);
        new Configuration(self::$mr, $url);
    }

    public function testMaxIncludedItems()
    {
        $this->expectException(InvalidConfigurationParameter::class);
        $c = new Configuration(self::$mr, self::$url, -123);
    }

    public function testRelationshipLimit()
    {
        $this->expectException(InvalidConfigurationParameter::class);
        $c = new Configuration(self::$mr, self::$url, 625, -25);
    }
}
