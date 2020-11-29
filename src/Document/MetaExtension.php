<?php

declare(strict_types=1);

namespace JSONAPI\Document;

/**
 * Trait MetaTrait
 *
 * @package JSONAPI
 */
trait MetaExtension
{

    /**
     * @var Meta|null
     */
    private ?Meta $meta = null;

    /**
     * @param Meta $meta
     */
    public function setMeta(Meta $meta): void
    {
        $this->meta = $meta;
    }

    /**
     * @return Meta
     */
    public function getMeta(): Meta
    {
        if ($this->meta === null) {
            $this->meta = new Meta();
        }
        return $this->meta;
    }

    /**
     * @return bool
     */
    public function hasMeta(): bool
    {
        return isset($this->meta) && !$this->meta->isEmpty();
    }
}
