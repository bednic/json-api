<?php

declare(strict_types=1);

namespace JSONAPI\Helper;

use JSONAPI\Document\Link;

/**
 * Trait LinksTrait
 *
 * @package JSONAPI
 */
trait LinksTrait
{

    /**
     * @var Link[]
     */
    protected array $links = [];

    /**
     * @param Link $link
     */
    public function setLink(Link $link): void
    {
        $this->links[$link->getKey()] = $link;
    }

    /**
     * @return Link[]
     */
    public function getLinks(): array
    {
        return $this->links;
    }

    /**
     * @return bool
     */
    public function hasLinks(): bool
    {
        return count($this->links) > 0;
    }
}
