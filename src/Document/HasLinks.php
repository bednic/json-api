<?php


namespace JSONAPI\Document;

/**
 * Interface HasLinks
 *
 * @package JSONAPI\Document
 */
interface HasLinks
{
    /**
     * @param Link[] $links
     */
    public function setLinks(array $links): void;

    /**
     * @param Link $link
     */
    public function addLink(Link $link): void;
}
