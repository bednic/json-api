<?php

declare(strict_types=1);

namespace JSONAPI\Document;

use JSONAPI\Configuration;
use JSONAPI\Encoding\AttributesProcessor;
use JSONAPI\Encoding\Encoder;
use JSONAPI\Encoding\LinksProcessor;
use JSONAPI\Encoding\MetaProcessor;
use JSONAPI\Encoding\RelationshipsProcessor;
use JSONAPI\Exception\Http\BadRequest;
use JSONAPI\URI\URIParser;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class DocumentBuilderFactory
 *
 * @package JSONAPI\Factory
 */
final class BuilderFactory
{
    private Configuration $configuration;
    /**
     * DocumentBuilderFactory constructor.
     *
     * @param Configuration $configuration
     */
    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return Builder
     * @throws BadRequest
     */
    public function create(ServerRequestInterface $request): Builder
    {
        $linkFactory = new LinkComposer($this->configuration->getBaseURL());
        $uri = (new URIParser($this->configuration))->parse($request);
        $encoder     = new Encoder(
            $this->configuration->getMetadataRepository(),
            $this->configuration->getLogger(),
            [
                new AttributesProcessor(
                    $this->configuration->getMetadataRepository(),
                    $this->configuration->getLogger(),
                    $uri->getFieldset()
                ),
                new RelationshipsProcessor(
                    $this->configuration->getMetadataRepository(),
                    $this->configuration->getLogger(),
                    $linkFactory,
                    $uri->getInclusion(),
                    $uri->getFieldset(),
                    $this->configuration->isRelationshipData(),
                    $this->configuration->getRelationshipLimit()
                ),
                new MetaProcessor(
                    $this->configuration->getMetadataRepository(),
                    $this->configuration->getLogger()
                ),
                new LinksProcessor($linkFactory)
            ]
        );
        $collector   = new InclusionCollector(
            $this->configuration->getMetadataRepository(),
            $encoder,
            $this->configuration->getMaxIncludedItems(),
            $this->configuration->getLogger()
        );
        return new Builder($encoder, $collector, $linkFactory, $uri, $this->configuration->getLogger());
    }
}
