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
use JSONAPI\Exception\QueryException;
use Slim\Psr7\Factory\UriFactory;

/**
 * Class LinkProvider
 *
 * @package JSONAPI
 */
class LinkProvider
{
    private const API_URL_ENV = "JSON_API_URL";

    const SELF = 'self';
    const RELATED = 'related';
    const FIRST = 'first';
    const LAST = 'last';
    const NEXT = 'next';
    const PREV = 'prev';

    private static $url = '';

    /**
     * @return string
     * @throws QueryException
     */
    public static function getAPIUrl(): string
    {
        if (!self::$url) {
            $uriFactory = new UriFactory();
            if (getenv(self::API_URL_ENV) !== false) {
                $uri = $uriFactory->createUri(getenv(self::API_URL_ENV));
                self::$url = (string)$uri . (preg_match('/\/$/', (string)$uri) === false ? '/' : '');
            } else {
                $uri = $uriFactory->createFromGlobals($_SERVER);
                self::$url = $uri->getScheme() . '://' . $uri->getHost()
                    . ($uri->getPort() ? ':' . $uri->getPort() : '') . '/';
            }
        }
        if (!filter_var(self::$url, FILTER_VALIDATE_URL)) {
            throw new QueryException("Bad API Url");
        }
        return self::$url;
    }

    /**
     * @return Link
     * @throws DocumentException
     * @throws QueryException
     */
    public static function createPrimaryDataLink(): Link
    {
        $url = QueryFactory::create();
        return new Link(self::SELF, self::getAPIUrl() . (string)$url->path);
    }

    /**
     * @param ResourceObjectIdentifier $resource
     * @param Relationship|null        $relationship
     * @return Link
     * @throws DocumentException
     * @throws QueryException
     */
    public static function createSelfLink(ResourceObjectIdentifier $resource, Relationship $relationship = null): Link
    {
        $url = self::getAPIUrl() . $resource->getType() . '/' . $resource->getId();
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
     * @throws QueryException
     */
    public static function createRelatedLink(ResourceObjectIdentifier $resource, Relationship $relationship): Link
    {
        $url = self::getAPIUrl() . $resource->getType() . '/' . $resource->getId() . '/' . $relationship->getKey();
        return new Link(self::RELATED, $url);
    }
}
