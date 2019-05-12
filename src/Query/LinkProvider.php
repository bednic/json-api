<?php
/**
 * Created by IntelliJ IDEA.
 * User: tomas
 * Date: 10.03.2019
 * Time: 0:22
 */

namespace JSONAPI\Query;

use JSONAPI\Document\Link;
use JSONAPI\Document\Relationship;
use JSONAPI\Document\ResourceObjectIdentifier;
use JSONAPI\Exception\DocumentException;

/**
 * Class LinkProvider
 *
 * @package JSONAPI
 */
class LinkProvider
{
    const SELF = 'self';
    const RELATED = 'related';
    const FIRST = 'first';
    const LAST = 'last';
    const NEXT = 'next';
    const PREV = 'prev';

    /**
     * @return string
     */
    public static function getUrl(): string
    {
        return getenv("JSON_API_URL") !== false ?
            (string)getenv("JSON_API_URL") : "$_SERVER[REQUEST_SCHEME]://$_SERVER[HTTP_HOST]/";
    }

    /**
     * @return Link
     * @throws DocumentException
     */
    public static function createPrimaryDataLink(): Link
    {
        $url = QueryFactory::create();
        return new Link(self::SELF, self::getUrl() . (string)$url->path);
    }

    /**
     * @param ResourceObjectIdentifier $resource
     * @param Relationship|null        $relationship
     * @return Link
     * @throws DocumentException
     */
    public static function createSelfLink(ResourceObjectIdentifier $resource, Relationship $relationship = null): Link
    {
        $url = self::getUrl() . $resource->getType() . '/' . $resource->getId();
        if ($relationship) {
            $url .= '/relationships/' . $relationship->getKey();
        }
        return new Link(self::SELF, $url);
    }

    /**
     * @param ResourceObjectIdentifier $resource
     * @param Relationship             $relationship
     * @return Link
     * @throws DocumentException
     */
    public static function createRelatedLink(ResourceObjectIdentifier $resource, Relationship $relationship): Link
    {
        $url = self::getUrl() . $resource->getType() . '/' . $resource->getId() . '/' . $relationship->getKey();
        return new Link(self::RELATED, $url);
    }
}
