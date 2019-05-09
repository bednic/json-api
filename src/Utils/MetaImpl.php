<?php


namespace JSONAPI\Utils;


use JSONAPI\Document\Meta;

trait MetaImpl
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
