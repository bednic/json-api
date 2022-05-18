<?php

declare(strict_types=1);

namespace JSONAPI\Document;

use JSONAPI\Exception\Document\ForbiddenCharacter;
use JSONAPI\Exception\Document\ForbiddenDataType;
use JSONAPI\URI\Fieldset\FieldsetInterface;
use JSONAPI\URI\Filtering\FilterInterface;
use JSONAPI\URI\Inclusion\InclusionInterface;
use JSONAPI\URI\Pagination\PaginationInterface;
use JSONAPI\URI\ParsedURI;
use JSONAPI\URI\Path\PathInterface;
use JSONAPI\URI\Sorting\SortInterface;

/**
 * Class LinkComposer
 *
 * @package JSONAPI\Factory
 */
class LinkComposer
{
    /**
     * @var string
     */
    private string $baseURL;

    /**
     * LinkComposer constructor.
     *
     * @param string $baseURL
     */
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
        $resource->setLink(new Link(Link::SELF, $this->getResourceLink($resource), $meta));
        return $resource;
    }

    /**
     * @param ResourceObjectIdentifier $resource
     *
     * @return string
     */
    private function getResourceLink(ResourceObjectIdentifier $resource): string
    {
        return $this->baseURL . '/' . rawurlencode($resource->getType()) . '/' . rawurlencode($resource->getId());
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
        $relationship->setLink(
            new Link(
                Link::SELF,
                $this->getResourceLink($resource) . '/relationships/' . $relationship->getKey(),
                $meta
            )
        );
        $relationship->setLink(
            new Link(
                Link::RELATED,
                $this->getResourceLink($resource) . '/' . $relationship->getKey(),
                $meta
            )
        );
        return $relationship;
    }

    /**
     * @param Document  $document
     * @param ParsedURI $parser
     *
     * @return Document
     * @throws ForbiddenCharacter
     * @throws ForbiddenDataType
     */
    public function setDocumentLinks(Document $document, ParsedURI $parser): Document
    {
        $path       = $parser->getPath();
        $filter     = $parser->getFilter();
        $inclusion  = $parser->getInclusion();
        $fieldset   = $parser->getFieldset();
        $sort       = $parser->getSort();
        $pagination = $parser->getPagination();
        if ($document->getData() instanceof ResourceCollection) {
            $document->setLink(
                $this->createDocumentLink(
                    Link::SELF,
                    $path,
                    $filter,
                    $inclusion,
                    $fieldset,
                    $pagination,
                    $sort
                )
            );
            $first = $pagination->first();
            $document->setLink(
                $this->createDocumentLink(
                    Link::FIRST,
                    $path,
                    $filter,
                    $inclusion,
                    $fieldset,
                    $first,
                    $sort
                )
            );
            if ($last = $pagination->last()) {
                $document->setLink(
                    $this->createDocumentLink(
                        Link::LAST,
                        $path,
                        $filter,
                        $inclusion,
                        $fieldset,
                        $last,
                        $sort
                    )
                );
            }
            if ($prev = $pagination->prev()) {
                $document->setLink(
                    $this->createDocumentLink(
                        Link::PREV,
                        $path,
                        $filter,
                        $inclusion,
                        $fieldset,
                        $prev,
                        $sort
                    )
                );
            }
            if ($next = $pagination->next()) {
                $document->setLink(
                    $this->createDocumentLink(
                        Link::NEXT,
                        $path,
                        $filter,
                        $inclusion,
                        $fieldset,
                        $next,
                        $sort
                    )
                );
            }
        } else {
            $document->setLink(
                $this->createDocumentLink(
                    Link::SELF,
                    $parser->getPath(),
                    null,
                    $parser->getInclusion(),
                    $parser->getFieldset(),
                    null,
                    null
                )
            );
        }
        return $document;
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
}
