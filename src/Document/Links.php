<?php
/**
 * Created by IntelliJ IDEA.
 * User: tomas
 * Date: 11.02.2019
 * Time: 15:10
 */

namespace OpenAPI\Document;


class Links
{
    const SELF = 'self';

    /**
     * @param ResourceIdentifier $from owning resource
     * @param string $to field name
     * @return array
     */
    public static function createRelationshipsLinks(ResourceIdentifier $from, string $to)
    {
        return [
            self::SELF => BASE_API_URL . '/' . $from->getType() . '/' . $from->getId() . '/relationships/' . $to
        ];
    }

    /**
     * @param ResourceIdentifier $resourceIdentifier
     * @return array
     */
    public static function createResourceLinks(ResourceIdentifier $resourceIdentifier)
    {
        return [
            self::SELF => BASE_API_URL . '/' . $resourceIdentifier->getType() . '/' . $resourceIdentifier->getId()
        ];
    }
}
