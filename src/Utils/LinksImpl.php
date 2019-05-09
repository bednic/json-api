<?php


namespace JSONAPI\Utils;

use JSONAPI\Document\Link;

trait LinksImpl
{

    /**
     * @var Link[]
     */
    protected $links = [];

    /**
     * @param Link[] $links
     */
    public function setLinks(array $links): void
    {
        foreach ($links as $link) {
            $this->links[$link->getKey()] = $link;
        }
    }

    /**
     * @param Link $link
     */
    public function addLink(Link $link): void
    {
        $this->links[$link->getKey()] = $link;
    }
}
