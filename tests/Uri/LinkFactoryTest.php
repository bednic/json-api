<?php

declare(strict_types=1);

namespace JSONAPI\Test\Uri;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Collections\ArrayCollection;
use JSONAPI\Document\Document;
use JSONAPI\Document\Id;
use JSONAPI\Document\Relationship;
use JSONAPI\Document\ResourceObject;
use JSONAPI\Document\Type;
use JSONAPI\Driver\AnnotationDriver;
use JSONAPI\Metadata\MetadataFactory;
use JSONAPI\Uri\LinkFactory;
use JSONAPI\Uri\UriParser;
use PHPUnit\Framework\TestCase;
use Roave\DoctrineSimpleCache\SimpleCacheAdapter;
use Slim\Psr7\Factory\ServerRequestFactory;

class LinkFactoryTest extends TestCase
{

    /**
     * @var UriParser
     */
    private static UriParser $up;

    public static function setUpBeforeClass(): void
    {
        $metadata = MetadataFactory::create(
            [RESOURCES . '/valid'],
            new SimpleCacheAdapter(new ArrayCache()),
            new AnnotationDriver()
        );
        $request  = ServerRequestFactory::createFromGlobals();
        self::$up = new UriParser($request, $metadata);
    }

    public function testSetDocumentLinks()
    {
        $document = new Document();
        LinkFactory::setDocumentLinks($document, self::$up);
        $links = $document->getLinks();
        $this->assertIsArray($links);
        $this->assertArrayHasKey(LinkFactory::SELF, $links);
        $self = $links[LinkFactory::SELF];
        $this->assertEquals(
            'http://unit.test.org/api/getter/uuid?include=collection&' .
            'fields%5Bresource%5D=publicProperty,privateProperty,relations',
            $self->getData()
        );
        $this->assertTrue(filter_var($self->getData(), FILTER_VALIDATE_URL, FILTER_NULL_ON_FAILURE) !== null);
    }

    public function testSetRelationshipLinks()
    {
        $resource     = new ResourceObject(new Type('resource'), new Id('id'));
        $relationship = new Relationship('test', null);
        LinkFactory::setRelationshipLinks($relationship, $resource);
        $links = $relationship->getLinks();
        $this->assertIsArray($links);
        $this->assertArrayHasKey(LinkFactory::SELF, $links);
        $self = $links[LinkFactory::SELF];
        $this->assertEquals('http://unit.test.org/api/resource/id/relationships/test', $self->getData());
        $this->assertTrue(filter_var($self->getData(), FILTER_VALIDATE_URL, FILTER_NULL_ON_FAILURE) !== null);
        $related = $links[LinkFactory::RELATED];
        $this->assertEquals('http://unit.test.org/api/resource/id/test', $related->getData());
        $this->assertTrue(filter_var($related->getData(), FILTER_VALIDATE_URL, FILTER_NULL_ON_FAILURE) !== null);
    }

    public function testSetResourceLink()
    {
        $resource = new ResourceObject(new Type('resource'), new Id('id'));
        LinkFactory::setResourceLink($resource);
        $links = $resource->getLinks();
        $this->assertIsArray($links);
        $this->assertArrayHasKey(LinkFactory::SELF, $links);
        $self = $links[LinkFactory::SELF];
        $this->assertEquals('http://unit.test.org/api/resource/id', $self->getData());
        $this->assertTrue(filter_var($self->getData(), FILTER_VALIDATE_URL, FILTER_NULL_ON_FAILURE) !== null);
    }

    public function testGetBaseUrl()
    {
        $base = LinkFactory::getBaseUrl();
        $this->assertIsString($base);
        $this->assertEquals('http://unit.test.org/api', $base);
    }
}
