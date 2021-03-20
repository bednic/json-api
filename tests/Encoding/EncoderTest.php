<?php

/**
 * Created by tomas
 * at 20.03.2021 22:20
 */

declare(strict_types=1);

namespace JSONAPI\Test\Encoding;

use JSONAPI\Driver\AnnotationDriver;
use JSONAPI\Encoding\AttributesProcessor;
use JSONAPI\Encoding\Encoder;
use JSONAPI\Encoding\LinksProcessor;
use JSONAPI\Encoding\MetaProcessor;
use JSONAPI\Encoding\RelationshipsProcessor;
use JSONAPI\Factory\LinkComposer;
use JSONAPI\Factory\MetadataFactory;
use JSONAPI\Metadata\MetadataRepository;
use JSONAPI\Test\Resources\Valid\GettersExample;
use JSONAPI\URI\Fieldset\FieldsetInterface;
use JSONAPI\URI\Fieldset\FieldsetParser;
use JSONAPI\URI\Inclusion\InclusionInterface;
use JSONAPI\URI\Inclusion\InclusionParser;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\Test\TestLogger;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Psr16Cache;

class EncoderTest extends TestCase
{
    private static MetadataRepository $metadata;
    private static FieldsetInterface $fieldset;
    private static InclusionInterface $inclusion;
    private static LinkComposer $linkFactory;
    private static LoggerInterface $logger;

    public static function setUpBeforeClass(): void
    {
        self::$metadata    = MetadataFactory::create(
            [RESOURCES . '/valid'],
            new Psr16Cache(new ArrayAdapter()),
            new AnnotationDriver()
        );
        self::$fieldset    = (new FieldsetParser())->parse([]);
        self::$inclusion   = (new InclusionParser())->parse('');
        $baseUrl           = 'http://unit.test.org/api';
        self::$linkFactory = new LinkComposer($baseUrl);
        self::$logger      = new TestLogger();
    }

    public function testIdentify()
    {
        $encoder  = new Encoder(
            self::$metadata, self::$logger, [
                               new AttributesProcessor(self::$metadata, self::$logger, self::$fieldset),
                               new RelationshipsProcessor(
                                   self::$metadata,
                                   self::$logger,
                                   self::$linkFactory,
                                   self::$inclusion,
                                   self::$fieldset,
                                   true,
                                   25
                               ),
                               new MetaProcessor(self::$metadata, self::$logger),
                               new LinksProcessor(self::$linkFactory)
                           ]
        );
        $object   = new GettersExample('id');
        $resource = $encoder->identify($object);
        $result = json_encode($resource);
        $check = '{"type":"getter","id":"id"}';
        $this->assertEquals($check,$result);
    }

    public function testEncode()
    {
        $encoder  = new Encoder(
            self::$metadata, self::$logger, [
            new AttributesProcessor(self::$metadata, self::$logger, self::$fieldset),
            new RelationshipsProcessor(
                self::$metadata,
                self::$logger,
                self::$linkFactory,
                self::$inclusion,
                self::$fieldset,
                true,
                25
            ),
            new MetaProcessor(self::$metadata, self::$logger),
            new LinksProcessor(self::$linkFactory)
        ]
        );
        $object   = new GettersExample('id');
        $resource = $encoder->encode($object);
        $result = json_encode($resource);
        $check = '{"type":"getter","id":"id","attributes":{"stringProperty":"string value","intProperty":1,"arrayProperty":[1,2,3],"boolProperty":true,"dtoProperty":{"stringProperty":"string-value","intProperty":1234,"boolProperty":true}},"relationships":{"relation":{"data":{"type":"relation","id":"relation1"},"links":{"self":"http:\/\/unit.test.org\/api\/getter\/id\/relationships\/relation","related":"http:\/\/unit.test.org\/api\/getter\/id\/relation"}},"collection":{"data":[{"type":"relation","id":"relation2"},{"type":"relation","id":"relation3"}],"links":{"self":"http:\/\/unit.test.org\/api\/getter\/id\/relationships\/collection","related":"http:\/\/unit.test.org\/api\/getter\/id\/collection"}},"doctrineCollection":{"data":[],"links":{"self":"http:\/\/unit.test.org\/api\/getter\/id\/relationships\/doctrineCollection","related":"http:\/\/unit.test.org\/api\/getter\/id\/doctrineCollection"}}},"links":{"self":"http:\/\/unit.test.org\/api\/getter\/id"}}';
        $this->assertEquals($check,$result);
    }
}
