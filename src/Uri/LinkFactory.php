<?php

/**
 * Created by IntelliJ IDEA.
 * User: tomas
 * Date: 10.03.2019
 * Time: 0:22
 */

namespace JSONAPI\Uri;

use JSONAPI\Document\Link;
use JSONAPI\Document\Meta;
use JSONAPI\Document\Relationship;
use JSONAPI\Document\ResourceObjectIdentifier;
use JSONAPI\Exception\Document\ForbiddenCharacter;
use JSONAPI\Exception\Document\ForbiddenDataType;
use JSONAPI\Exception\InvalidArgumentException;
use Slim\Psr7\Factory\UriFactory;

/**
 * Class LinkFactory
 *
 * @package JSONAPI
 */
class LinkFactory
{
    private const API_URL_ENV = "JSON_API_URL";

    public const SELF = 'self';
    public const RELATED = 'related';
    public const FIRST = 'first';
    public const LAST = 'last';
    public const NEXT = 'next';
    public const PREV = 'prev';

    private static $url = '';

    /**
     * @return string
     * @throws InvalidArgumentException
     */
    public static function getAPIUrl(): string
    {
        if (!self::$url) {
            $uriFactory = new UriFactory();
            $url = getenv(self::API_URL_ENV);
            if (!$url || !filter_var($url, FILTER_VALIDATE_URL)) {
                throw new InvalidArgumentException("Invalid URL passed from ENV");
            }
            $uri = $uriFactory->createUri(getenv(self::API_URL_ENV));
            self::$url = preg_replace('/\/$/', '', (string)$uri);
        }
        return self::$url;
    }

    /**
     * @param ResourceObjectIdentifier $resource
     * @param Relationship             $relationship
     *
     * @param Meta|null                $meta
     *
     * @return Link
     * @throws ForbiddenCharacter
     * @throws ForbiddenDataType
     * @throws InvalidArgumentException
     */
    public static function createRelationshipLink(
        ResourceObjectIdentifier $resource,
        Relationship $relationship,
        Meta $meta = null
    ) {
        return new Link(
            self::SELF,
            (self::createSelfLink($resource))->getData() . '/relationships/' . $relationship->getKey(),
            $meta
        );
    }

    /**
     * @param ResourceObjectIdentifier $resource
     * @param Meta|null                $meta
     *
     * @return Link
     * @throws ForbiddenCharacter
     * @throws ForbiddenDataType
     * @throws InvalidArgumentException
     */
    public static function createSelfLink(ResourceObjectIdentifier $resource, Meta $meta = null): Link
    {
        $url = self::getAPIUrl() . '/' . $resource->getType() . '/' . $resource->getId();
        return new Link(self::SELF, $url, $meta);
    }

    /**
     * @param ResourceObjectIdentifier $resource
     * @param Relationship             $relationship
     *
     * @param Meta|null                $meta
     *
     * @return Link
     * @throws ForbiddenCharacter
     * @throws ForbiddenDataType
     * @throws InvalidArgumentException
     */
    public static function createRelatedLink(
        ResourceObjectIdentifier $resource,
        Relationship $relationship,
        Meta $meta = null
    ): Link {
        $url = self::getAPIUrl()
            . '/' . $resource->getType()
            . '/' . $resource->getId()
            . '/' . $relationship->getKey();
        return new Link(self::RELATED, $url, $meta);
    }
}
