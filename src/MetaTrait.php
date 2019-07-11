<?php


namespace JSONAPI;

use JSONAPI\Document\Meta;

/**
 * Trait MetaImpl
 *
 * @package JSONAPI\Utils
 */
trait MetaTrait
{

    /**
     * @var Meta
     */
    protected $meta = null;

    /**
     * @param Meta $meta
     */
    public function setMeta(Meta $meta): void
    {
        $this->meta = $meta;
    }
}
