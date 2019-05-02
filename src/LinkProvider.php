<?php
/**
 * Created by IntelliJ IDEA.
 * User: tomas
 * Date: 10.03.2019
 * Time: 0:22
 */

namespace JSONAPI;


use JSONAPI\Document\Resource;
use JSONAPI\Document\ResourceIdentifier;
use JSONAPI\Filter\URLFactory;

/**
 * Class LinkProvider
 * @package JSONAPI
 */
class LinkProvider
{
    const SELF = 'self';
    const RELATED = 'related';

    /**
     * @param ResourceIdentifier $resource owning resource
     * @param string $relationshipFieldName field name
     * @return array
     */
    public static function createRelationshipsLinks(ResourceIdentifier $resource, string $relationshipFieldName)
    {
        return [
            self::SELF => self::getUrl() . $resource->getType() . '/' . $resource->getId() . '/relationships/' . $relationshipFieldName,
            self::RELATED => self::getUrl() . $resource->getType() . '/' . $resource->getId() . '/' . $relationshipFieldName
        ];
    }

    /**
     * @param ResourceIdentifier $resourceIdentifier
     * @return array
     */
    public static function createResourceLinks(ResourceIdentifier $resourceIdentifier)
    {
        return [self::SELF, self::getUrl() . $resourceIdentifier->getType() . '/' . $resourceIdentifier->getId()];
    }

    /**
     * @return array
     */
    public static function createPrimaryDataLink(): array
    {
        $url = URLFactory::create();
        return [self::SELF, self::getUrl() . (string) $url->endpoint];
    }

    public static function getUrl()
    {
        return getenv("API_ENV_URL") !== false ?
            getenv("API_ENV_URL") : "$_SERVER[REQUEST_SCHEME]://$_SERVER[HTTP_HOST]/";
    }

}
