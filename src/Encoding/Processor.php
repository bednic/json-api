<?php

/**
 * Created by tomas
 * at 20.03.2021 15:36
 */

declare(strict_types=1);

namespace JSONAPI\Encoding;

use JSONAPI\Document\ResourceObject;
use JSONAPI\Document\ResourceObjectIdentifier;
use JSONAPI\Exception\JsonApiException;

/**
 * Interface Processor
 *
 * @package JSONAPI\Encoding
 */
interface Processor
{
    /**
     * @param ResourceObjectIdentifier|ResourceObject $resource
     * @param object                                  $object
     *
     * @return ResourceObjectIdentifier|ResourceObject
     * @throws JsonApiException
     */
    public function process(ResourceObjectIdentifier | ResourceObject $resource, object $object): ResourceObjectIdentifier | ResourceObject;
}
