<?php

/**
 * Created by IntelliJ IDEA.
 * User: tomas
 * Date: 24.04.2019
 * Time: 15:54
 */

namespace JSONAPI\Test;

use Doctrine\Common\Collections\ArrayCollection;
use JSONAPI\Document\Relationship;
use JSONAPI\Document\ResourceObject;
use JSONAPI\Metadata\Encoder;
use JSONAPI\Metadata\MetadataFactory;
use JSONAPI\Uri\Query;
use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\ServerRequestFactory;

/**
 * Class EncoderTest
 *
 * @package JSONAPI\Test
 */
class EncoderTest extends TestCase
{
    /**
     * @var MetadataFactory
     */
    private static $factory;

    /**
     * @var Query
     */
    private static $query;

    /**
     * @var ObjectExample
     */
    private static $instance;

    public static function setUpBeforeClass(): void
    {
        $request = ServerRequestFactory::createFromGlobals();
        self::$query = new Query($request);
        self::$factory = new MetadataFactory(__DIR__ . '/resources/');
        $relation = new RelationExample();
        $instance = new ObjectExample();
        $instance->setRelations([$relation]);
        self::$instance = $instance;
    }

    public function testConstruct()
    {
        $encoder = new Encoder(self::$factory, self::$query);
        $this->assertInstanceOf(Encoder::class, $encoder);
        return $encoder;
    }

    /**
     * @depends testConstruct
     */
    public function testGetType(Encoder $encoder)
    {
        $resource = $encoder->encode(self::$instance);
        $this->assertEquals('resource', $resource->getType());
    }

    /**
     * @depends testConstruct
     */
    public function testEncode(Encoder $encoder)
    {
        /** @var ResourceObject $resource */
        $resource = $encoder->encode(self::$instance);
        $this->assertInstanceOf(ResourceObject::class, $resource);

        /** @var Relationship $relation */
        $relation = $resource->getRelationship('relations');
        $this->assertInstanceOf(Relationship::class, $relation);
        $this->assertEquals(
            trim(file_get_contents(__DIR__ . '/resources/resource.json')),
            json_encode($resource)
        );
    }

    /**
     * @depends testConstruct
     */
    public function testGetId(Encoder $encoder)
    {
        $resource = $encoder->encode(self::$instance);
        $this->assertEquals('uuid', $resource->getId());
    }
}
