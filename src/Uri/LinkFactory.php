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
use JSONAPI\Uri\Fieldset\FieldsetInterface;
use JSONAPI\Uri\Filtering\FilterInterface;
use JSONAPI\Uri\Inclusion\InclusionInterface;
use JSONAPI\Uri\Pagination\PaginationInterface;
use JSONAPI\Uri\Path\PathInterface;
use JSONAPI\Uri\Sorting\SortInterface;

/**
 * Class LinkFactory
 *
 * @package JSONAPI
 */
class LinkFactory
{
    public const API_URL_ENV = "JSON_API_URL";

    public const SELF = 'self';
    public const RELATED = 'related';
    public const FIRST = 'first';
    public const LAST = 'last';
    public const NEXT = 'next';
    public const PREV = 'prev';

    private string $url = 'http://localhost';

    public function __construct()
    {
        $this->url = getenv(self::API_URL_ENV) ?? 'http://localhost';
    }

    /**
     * @param ResourceObjectIdentifier $resource
     * @param Meta|null                $meta
     *
     * @return Link
     * @throws ForbiddenCharacter
     * @throws ForbiddenDataType
     */
    public function getResourceLinkage(ResourceObjectIdentifier $resource, Meta $meta = null): Link
    {
        return new Link(self::SELF, $this->url . '/' . $resource->getType() . '/' . $resource->getId(), $meta);
    }

    /**
     * @param Relationship             $relationship
     * @param ResourceObjectIdentifier $identifier
     * @param Meta|null                $meta
     *
     * @return Link
     * @throws ForbiddenCharacter
     * @throws ForbiddenDataType
     */
    public function getRelationshipLink(
        Relationship $relationship,
        ResourceObjectIdentifier $identifier,
        Meta $meta = null
    ): Link {
        return new Link(
            self::SELF,
            $this->getResourceLinkage($identifier)->getData() . '/relationships/' . $relationship->getKey(),
            $meta
        );
    }

    /**
     * @param Relationship             $relationship
     * @param ResourceObjectIdentifier $identifier
     * @param Meta|null                $meta
     *
     * @return Link
     * @throws ForbiddenCharacter
     * @throws ForbiddenDataType
     */
    public function getRelationLink(
        Relationship $relationship,
        ResourceObjectIdentifier $identifier,
        Meta $meta = null
    ): Link {
        return new Link(
            self::RELATED,
            $this->getResourceLinkage($identifier)->getData() . '/' . $relationship->getKey(),
            $meta
        );
    }

    /**
     * @param string                   $type
     * @param PathInterface            $path
     * @param FilterInterface|null     $filter
     * @param InclusionInterface|null  $inclusion
     * @param FieldsetInterface|null   $fieldset
     * @param PaginationInterface|null $pagination
     * @param SortInterface|null       $sort
     *
     * @return Link
     * @throws ForbiddenCharacter
     * @throws ForbiddenDataType
     */
    public function getDocumentLink(
        string $type,
        PathInterface $path,
        ?FilterInterface $filter,
        ?InclusionInterface $inclusion,
        ?FieldsetInterface $fieldset,
        ?PaginationInterface $pagination,
        ?SortInterface $sort
    ): Link {
        $link = $this->url . (string)$path;
        $mark = '?';
        if (strlen((string)$filter)) {
            $link .= $mark . $filter;
            $mark = '&';
        }
        if (strlen((string)$inclusion)) {
            $link .= $mark . $inclusion;
            $mark = '&';
        }
        if (strlen((string)$fieldset)) {
            $link .= $mark . $fieldset;
            $mark = '&';
        }
        if (strlen((string)$pagination)) {
            $link .= $mark . $pagination;
            $mark = '&';
        }
        if (strlen((string)$sort)) {
            $link .= $mark . $sort;
        }
        return new Link($type, $link);
    }
}
