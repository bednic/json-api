<?php
/**
 * Created by IntelliJ IDEA.
 * User: tomas
 * Date: 24.04.2019
 * Time: 15:54
 */

namespace Test\JSONAPI;

use JSONAPI\Document\Document;
use JSONAPI\Document\Relationship;
use JSONAPI\Document\Resource;
use JSONAPI\Encoder;
use JSONAPI\EncoderOptions;
use JSONAPI\MetadataFactory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

class EncoderTest extends TestCase
{
    /**
     * @var MetadataFactory
     */
    private static $factory;

    private static $options;

    private static $instance;

    public static function setUpBeforeClass(): void
    {
        self::$factory = new MetadataFactory(__DIR__ . '/resources/');
        self::$options = new EncoderOptions();
        $relation = new RelationExample();
        $instance = new ObjectExample();
        $instance->setRelations([$relation]);
        self::$instance = $instance;
    }

    public function test__construct()
    {
        $encoder = new Encoder(self::$factory);
        $this->assertInstanceOf(Encoder::class, $encoder);
        return $encoder;
    }

    /**
     * @depends test__construct
     */
    public function testDecode(Encoder $encoder)
    {
        $request = $this->createMock(RequestInterface::class);
        $request->method('getBody')
            ->willReturn(file_get_contents(__DIR__ . '/resources/request.json'));
        $request->method('getHeader')
            ->willReturn([Document::MEDIA_TYPE]);

        $document = $encoder->decode($request);
        $this->assertInstanceOf(Document::class, $document);
        $this->assertInstanceOf(Resource::class, $document->getData());
        $resource = $document->getData();
        $this->assertEquals('resource', $resource->getType());
        $this->assertEquals('uuid', $resource->getId());
        $this->assertEquals('public-value',
            $resource->getAttribute('publicProperty')->getValue());
        $this->assertEquals('private-value',
            $resource->getAttribute('privateProperty')->getValue());
        $this->assertEquals('relation-uuid',
            $resource->getRelationship('relations')->getData()->first()->getId()
        );
    }

    /**
     * @depends test__construct
     */
    public function testGetType(Encoder $encoder)
    {
        $resource = $encoder->encode(self::$instance);
        $this->assertEquals('resource', $resource->getType());
    }

    /**
     * @depends test__construct
     */
    public function testEncode(Encoder $encoder)
    {
        /** @var Resource $resource */
        $resource = $encoder->encode(self::$instance, new EncoderOptions(true));
        $this->assertInstanceOf(Resource::class, $resource);

        /** @var Relationship $relation */
        $relation = $resource->getRelationship('relations');
        $this->assertInstanceOf(Relationship::class, $relation);
        $this->assertEquals('{"type":"resource","id":"uuid","attributes":{"publicProperty":"public-value","privateProperty":"private-value","readOnlyProperty":"read-only-value"},"relationships":{"relations":{"data":[{"type":"resource-relation","id":"relation-uuid"}],"links":{"self":"http:\/\/unit.test.org\/resource\/uuid\/relationships\/relations","related":"http:\/\/unit.test.org\/resource\/uuid\/relations"}}}}',
            json_encode($resource));

    }

    /**
     * @depends test__construct
     */
    public function testGetId(Encoder $encoder)
    {
        $resource = $encoder->encode(self::$instance);
        $this->assertEquals('uuid', $resource->getId());
    }
}
