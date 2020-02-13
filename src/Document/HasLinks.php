<?php

declare(strict_types=1);

namespace JSONAPI\Document;

/**
 * Interface HasLinks
 *
 * @package JSONAPI\Document
 */
interface HasLinks
{
    /**
     * @param Link $link
     */
    public function setLink(Link $link): void;

    /**
     * @return array
     */
    public function getLinks(): array;

    /**
     * @return bool
     */
    public function hasLinks(): bool;
}
