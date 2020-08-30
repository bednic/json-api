<?php

declare(strict_types=1);

namespace JSONAPI\Uri;

use JSONAPI\Document\Document;
use JSONAPI\Document\Link;
use JSONAPI\Document\Meta;
use JSONAPI\Document\Relationship;
use JSONAPI\Document\ResourceCollection;
use JSONAPI\Document\ResourceObject;
use JSONAPI\Document\ResourceObjectIdentifier;
use JSONAPI\Exception\Document\ForbiddenCharacter;
use JSONAPI\Exception\Document\ForbiddenDataType;
use JSONAPI\Exception\Http\BadRequest;
use JSONAPI\Uri\Fieldset\FieldsetInterface;
use JSONAPI\Uri\Filtering\FilterInterface;
use JSONAPI\Uri\Inclusion\InclusionInterface;
use JSONAPI\Uri\Pagination\PaginationInterface;
use JSONAPI\Uri\Path\PathInterface;
use JSONAPI\Uri\Sorting\SortInterface;

/**
 * Class LinkFactory
 *
 * @package JSONAPI\URI
 */
class LinkFactory
{

    public const SELF = 'self';
    public const RELATED = 'related';
    public const FIRST = 'first';
    public const LAST = 'last';
    public const NEXT = 'next';
    public const PREV = 'prev';

    private string $baseURL;

    public function __construct(string $baseURL)
    {
        $this->baseURL = $baseURL;
    }

    /**
     * @param ResourceObject $resource
     * @param Meta|null      $meta
     *
     * @return ResourceObject
     * @throws ForbiddenCharacter
     * @throws ForbiddenDataType
     */
    public function setResourceLink(ResourceObject $resource, Meta $meta = null): ResourceObject
    {
        $resource->setLink(new Link(self::SELF, self::getResourceLink($resource), $meta));
        return $resource;
    }

    /**
     * @param ResourceObjectIdentifier $resource
     *
     * @return string
     */
    private function getResourceLink(ResourceObjectIdentifier $resource): string
    {
        return $this->baseURL . '/' . $resource->getType() . '/' . $resource->getId();
    }

    /**
     * @param Relationship   $relationship
     * @param ResourceObject $resource
     * @param Meta|null      $meta
     *
     * @return Relationship
     * @throws ForbiddenCharacter
     * @throws ForbiddenDataType
     */
    public function setRelationshipLinks(
        Relationship $relationship,
        ResourceObject $resource,
        Meta $meta = null
    ): Relationship {
        $relationship->setLink(new Link(
            self::SELF,
            $this->getResourceLink($resource) . '/relationships/' . $relationship->getKey(),
            $meta
        ));
        $relationship->setLink(new Link(
            self::RELATED,
            $this->getResourceLink($resource) . '/' . $relationship->getKey(),
            $meta
        ));
        return $relationship;
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
    private function createDocumentLink(
        string $type,
        PathInterface $path,
        ?FilterInterface $filter,
        ?InclusionInterface $inclusion,
        ?FieldsetInterface $fieldset,
        ?PaginationInterface $pagination,
        ?SortInterface $sort
    ): Link {
        $url  = $this->baseURL;
        $link = $url . (string)$path;
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

    /**
     * @param Document  $document
     * @param UriParser $parser
     *
     * @return Document
     * @throws ForbiddenCharacter
     * @throws ForbiddenDataType
     * @throws BadRequest
     */
    public function setDocumentLinks(Document $document, UriParser $parser): Document
    {
        $path       = $parser->getPath();
        $filter     = $parser->getFilter();
        $inclusion  = $parser->getInclusion();
        $fieldset   = $parser->getFieldset();
        $sort       = $parser->getSort();
        $pagination = $parser->getPagination();
        if ($document->getData() instanceof ResourceCollection) {
            $document->setLink(self::createDocumentLink(
                LinkFactory::SELF,
                $path,
                $filter,
                $inclusion,
                $fieldset,
                $pagination,
                $sort
            ));
            if ($first = $pagination->first()) {
                $document->setLink(self::createDocumentLink(
                    LinkFactory::FIRST,
                    $path,
                    $filter,
                    $inclusion,
                    $fieldset,
                    $first,
                    $sort
                ));
            }
            if ($last = $pagination->last()) {
                $document->setLink(self::createDocumentLink(
                    LinkFactory::LAST,
                    $path,
                    $filter,
                    $inclusion,
                    $fieldset,
                    $last,
                    $sort
                ));
            }
            if ($prev = $pagination->prev()) {
                $document->setLink(self::createDocumentLink(
                    LinkFactory::PREV,
                    $path,
                    $filter,
                    $inclusion,
                    $fieldset,
                    $prev,
                    $sort
                ));
            }
            if ($next = $pagination->next()) {
                $document->setLink(self::createDocumentLink(
                    LinkFactory::NEXT,
                    $path,
                    $filter,
                    $inclusion,
                    $fieldset,
                    $next,
                    $sort
                ));
            }
        } else {
            $document->setLink(self::createDocumentLink(
                LinkFactory::SELF,
                $parser->getPath(),
                null,
                $parser->getInclusion(),
                $parser->getFieldset(),
                null,
                null
            ));
        }
        return $document;
    }
}
