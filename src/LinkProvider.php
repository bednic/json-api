<?php
/**
 * Created by IntelliJ IDEA.
 * User: tomas
 * Date: 10.03.2019
 * Time: 0:22
 */

namespace JSONAPI;


use JSONAPI\Document\ResourceIdentifier;

class LinkProvider
{
    const SELF = 'self';
    const RELATED = 'related';

    private $url;

    /**
     * LinkProvider constructor.
     * @param string $APIUrl
     */
    public function __construct(string $APIUrl)
    {
        $this->url = $APIUrl;
    }

    /**
     * @param ResourceIdentifier $resource owning resource
     * @param string             $relationshipFieldName field name
     * @return array
     */
    public function createRelationshipsLinks(ResourceIdentifier $resource, string $relationshipFieldName)
    {
        return [
            self::SELF => $this->url . $resource->getType() . '/' . $resource->getId() . '/relationships/' . $relationshipFieldName,
            self::RELATED => $this->url . $resource->getType() . '/' . $resource->getId() . '/' . $relationshipFieldName
        ];
    }

    /**
     * @param ResourceIdentifier $resourceIdentifier
     * @return array
     */
    public function createResourceLinks(ResourceIdentifier $resourceIdentifier)
    {
        return [
            self::SELF => $this->url . $resourceIdentifier->getType() . '/' . $resourceIdentifier->getId()
        ];
    }

    /**
     * @return array
     */
    public function createPrimaryDataLink()
    {
        $uri = "$_SERVER[REQUEST_SCHEME]://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $parsed = parse_url($uri);
        return [self::SELF, $this->url . substr($parsed["path"], 1) . (isset($parsed["query"])?"?".$parsed["query"]:"")];
    }

}
