<?php

declare(strict_types=1);

namespace JSONAPI\Test\URI\Filtering\Builder;

use JSONAPI\Driver\SchemaDriver;
use JSONAPI\Factory\MetadataFactory;
use JSONAPI\Metadata\MetadataRepository;
use JSONAPI\URI\Filtering\Builder\DoctrineQueryExpressionBuilder;
use JSONAPI\URI\Path\PathInterface;
use JSONAPI\URI\Path\PathParser;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Psr16Cache;

class DoctrineQueryExpressionBuilderTest extends TestCase
{

    private static MetadataRepository $mr;
    /**
     * @var PathInterface
     */
    private static PathInterface $path;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass(); // TODO: Change the autogenerated stub
        self::$mr = MetadataFactory::create(
            [RESOURCES . '/valid'],
            new Psr16Cache(new ArrayAdapter()),
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
            'prop'     => 'relation.example'
        ];
        $this->assertEquals('prop.intProperty', $identifier);
        $this->assertEquals($joins, $exp->getRequiredJoins());

        $identifier = $exp->parseIdentifier('relation.example');
        $this->assertEquals('relation.example', $identifier);
        $this->assertEquals($joins, $exp->getRequiredJoins());
    }
}
