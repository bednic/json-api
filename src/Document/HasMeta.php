<?php

declare(strict_types=1);

namespace JSONAPI\Document;

/**
 * Interface HasMeta
 *
 * @package JSONAPI\Document
 */
interface HasMeta
{
    /**
     * @param Meta $meta
     */
    public function setMeta(Meta $meta): void;

    /**
     * @return Meta
     */
    public function getMeta(): Meta;
}
