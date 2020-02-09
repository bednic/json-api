<?php

namespace JSONAPI\Test\Metadata;

use Doctrine\Common\Cache\ArrayCache;
use JSONAPI\Document\ResourceObject;
use JSONAPI\Document\ResourceObjectIdentifier;
use JSONAPI\Driver\AnnotationDriver;
use JSONAPI\Metadata\Encoder;
use JSONAPI\Metadata\MetadataFactory;
use JSONAPI\Test\Resources\Valid\GettersExample;
use JSONAPI\Uri\Fieldset\FieldsetParser;
use PHPUnit\Framework\TestCase;
use Roave\DoctrineSimpleCache\SimpleCacheAdapter;

class EncoderTest extends TestCase
{

    public function testConstruct()
    {
        $fieldset = (new FieldsetParser())->parse([]);
        $metadata = MetadataFactory::create(
            RESOURCES . '/valid',
            new SimpleCacheAdapter(new ArrayCache()),
            new AnnotationDriver()
        );
        $encoder = new Encoder($metadata, $fieldset);
        $this->assertInstanceOf(Encoder::class, $encoder);
        return $encoder;
    }

    /**
     * @depends testConstruct
     */
    public function testIdentify(Encoder $encoder)
    {
        $object = new GettersExample('id');
        $identifier = $encoder->getIdentifier($object);
        $this->assertInstanceOf(ResourceObjectIdentifier::class, $identifier);
    }
    /**
     * @depends testConstruct
     */
    public function testEncode(Encoder $encoder)
    {
        $object = new GettersExample('id');
        $resource = $encoder->getResource($object);
        $this->assertInstanceOf(ResourceObject::class, $resource);
    }

    /**
     * @depends testConstruct
     */
    public function testSetRelationshipLimit(Encoder $encoder)
    {
        $encoder->setRelationshipLimit(100);
        $this->expectException(\TypeError::class);
        $encoder->setRelationshipLimit('asdf');
    }

    /**
     * @depends testConstruct
     */
    public function testGetRelationshipLimit(Encoder $encoder)
    {
        $encoder->setRelationshipLimit(1);
        $this->assertIsInt($encoder->getRelationshipLimit());
        $this->assertEquals(1, $encoder->getRelationshipLimit());
    }
}
