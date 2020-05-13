<?php

declare(strict_types=1);

namespace JSONAPI\Test\Uri\Filtering\Builder;

use Doctrine\Common\Cache\ArrayCache;
use JSONAPI\Driver\SchemaDriver;
use JSONAPI\Metadata\MetadataFactory;
use JSONAPI\Uri\Filtering\Builder\DoctrineQueryExpressionBuilder;
use JSONAPI\Uri\Path\PathInterface;
use JSONAPI\Uri\Path\PathParser;
use PHPUnit\Framework\TestCase;
use Roave\DoctrineSimpleCache\SimpleCacheAdapter;

class DoctrineQueryExpressionBuilderTest extends TestCase
{

    private static \JSONAPI\Metadata\MetadataRepository $mr;
    /**
     * @var PathInterface
     */
    private static PathInterface $path;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass(); // TODO: Change the autogenerated stub
        self::$mr = MetadataFactory::create(
            [RESOURCES . '/valid'],
            new SimpleCacheAdapter(new ArrayCache()),
            new SchemaDriver()
        );
        self::$path = (new PathParser(self::$mr, 'GET'))->parse('/getter');
    }

    public function testParseIdentifier()
    {
        $exp = new DoctrineQueryExpressionBuilder(self::$mr, self::$path);
        $identifier = $exp->parseIdentifier('relation.example.intProperty');
        $joins = [
            'relation' => 'getter.relation',
            'prop' => 'relation.example'
        ];
        $this->assertEquals('prop.intProperty', $identifier);
        $this->assertEquals($joins, $exp->getRequiredJoins());

        $identifier = $exp->parseIdentifier('relation.example');
        $this->assertEquals('relation.example', $identifier);
        $this->assertEquals($joins, $exp->getRequiredJoins());
    }
}
