<?php

declare(strict_types=1);

namespace JSONAPI\Test\Factory;

use Doctrine\Common\Cache\ArrayCache;
use JSONAPI\Document\Document;
use JSONAPI\Document\Id;
use JSONAPI\Document\Link;
use JSONAPI\Document\Relationship;
use JSONAPI\Document\ResourceObject;
use JSONAPI\Document\Type;
use JSONAPI\Driver\AnnotationDriver;
use JSONAPI\Factory\LinkComposer;
use JSONAPI\Factory\MetadataFactory;
use JSONAPI\URI\URIParser;
use PHPUnit\Framework\TestCase;
use Roave\DoctrineSimpleCache\SimpleCacheAdapter;
use Slim\Psr7\Factory\ServerRequestFactory;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Psr16Cache;

class LinkComposerTest extends TestCase
{
    /**
     * @var URIParser
     */
    private static URIParser $up;
    private static string $baseURL;

    public static function setUpBeforeClass(): void
    {
        $metadata = MetadataFactory::create(
            [RESOURCES . '/valid'],
            new Psr16Cache(new ArrayAdapter()),
            new AnnotationDriver()
        );
        $request  = ServerRequestFactory::createFromGlobals();
        self::$baseURL = 'http://unit.test.org/api';
        self::$up = new URIParser($request, $metadata, self::$baseURL);
    }

    public function testSetDocumentLinks()
    {
        $document = new Document();
        $factory = new LinkComposer(self::$baseURL);
        $factory->setDocumentLinks($document, self::$up);
        $links = $document->getLinks();
        $this->assertIsArray($links);
        $this->assertArrayHasKey(Link::SELF, $links);
        $self = $links[Link::SELF];
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
        $relationship = new Relationship('test');
        $relationship->setData(null);
        $factory = new LinkComposer(self::$baseURL);
        $factory->setRelationshipLinks($relationship, $resource);
        $links = $relationship->getLinks();
        $this->assertIsArray($links);
        $this->assertArrayHasKey(Link::SELF, $links);
        $self = $links[Link::SELF];
        $this->assertEquals('http://unit.test.org/api/resource/id/relationships/test', $self->getData());
        $this->assertTrue(filter_var($self->getData(), FILTER_VALIDATE_URL, FILTER_NULL_ON_FAILURE) !== null);
        $related = $links[Link::RELATED];
        $this->assertEquals('http://unit.test.org/api/resource/id/test', $related->getData());
        $this->assertTrue(filter_var($related->getData(), FILTER_VALIDATE_URL, FILTER_NULL_ON_FAILURE) !== null);
    }

    public function testSetResourceLink()
    {
        $resource = new ResourceObject(new Type('resource'), new Id('id'));
        $factory = new LinkComposer(self::$baseURL);
        $factory->setResourceLink($resource);
        $links = $resource->getLinks();
        $this->assertIsArray($links);
        $this->assertArrayHasKey(Link::SELF, $links);
        $self = $links[Link::SELF];
        $this->assertEquals('http://unit.test.org/api/resource/id', $self->getData());
        $this->assertTrue(filter_var($self->getData(), FILTER_VALIDATE_URL, FILTER_NULL_ON_FAILURE) !== null);
    }
}
