<?php

/**
 * Created by tomas
 * at 20.03.2021 21:05
 */

declare(strict_types=1);

namespace JSONAPI\Encoding;

use JSONAPI\Document\ResourceObject;
use JSONAPI\Document\ResourceObjectIdentifier;
use JSONAPI\Document\LinkComposer;

/**
 * Class LinksProcessor
 *
 * @package JSONAPI\Encoding
 */
class LinksProcessor implements Processor
{
    private LinkComposer $linkFactory;

    /**
     * LinksProcessor constructor.
     *
     * @param LinkComposer $linkFactory
     */
    public function __construct(LinkComposer $linkFactory)
    {
        $this->linkFactory = $linkFactory;
    }

    public function process(
        ResourceObjectIdentifier | ResourceObject $resource,
        object $object
    ): ResourceObjectIdentifier | ResourceObject {
        if ($resource instanceof ResourceObject) {
            $resource = $this->linkFactory->setResourceLink($resource);
        }
        return $resource;
    }
}
