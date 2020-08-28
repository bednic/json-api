<?php

declare(strict_types=1);

namespace JSONAPI\Test\Metadata;

use Doctrine\Common\Cache\ArrayCache;
use JSONAPI\Document\Meta;
use JSONAPI\Document\ResourceObject;
use JSONAPI\Document\ResourceObjectIdentifier;
use JSONAPI\Driver\AnnotationDriver;
use JSONAPI\Metadata\Encoder;
use JSONAPI\Metadata\MetadataFactory;
use JSONAPI\Test\Resources\Valid\GettersExample;
use JSONAPI\Test\Resources\Valid\MetaExample;
use JSONAPI\Uri\Fieldset\FieldsetParser;
use JSONAPI\Uri\Inclusion\InclusionParser;
use PHPUnit\Framework\TestCase;
use Roave\DoctrineSimpleCache\SimpleCacheAdapter;

class EncoderTest extends TestCase
{

    public function testConstruct()
    {
        $fieldset = (new FieldsetParser())->parse([]);
        $inc = (new InclusionParser())->parse('');
        $metadata = MetadataFactory::create(
            [RESOURCES . '/valid'],
            new SimpleCacheAdapter(new ArrayCache()),
            new AnnotationDriver()
        );
        $encoder = new Encoder($metadata, $fieldset, $inc);
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
    public function testRelationshipMetaEncode(Encoder $encoder)
    {
        $object = new MetaExample('meta');
        $resource = $encoder->getResource($object);
        $this->assertInstanceOf(ResourceObject::class, $resource);
        $this->assertFalse($resource->jsonSerialize()['relationships']['relation']->getMeta()->isEmpty());
    }
}
