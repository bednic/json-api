<?php

declare(strict_types=1);

namespace JSONAPI\Helper;

use JSONAPI\Document\Meta;

/**
 * Trait MetaTrait
 *
 * @package JSONAPI
 */
trait MetaTrait
{

    /**
     * @var Meta
     */
    protected ?Meta $meta = null;

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
}
