<?php

/**
 * Created by tomas.benedikt@gmail.com
 */

declare(strict_types=1);

namespace JSONAPI\Annotation;

/**
 * Class Resource
 *
 * @package JSONAPI\Annotation
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
final class Resource
{
    /**
     * @var Meta|null
     */
    public ?Meta $meta = null;
    public ?string $type = null;
    public bool $readOnly = false;

    /**
     * Resource constructor.
     *
     * @param string|null $type
     * @param bool        $readOnly
     */
    public function __construct(
        ?string $type = null,
        bool $readOnly = false
    ) {
        $this->readOnly = $readOnly;
        $this->type = $type;
    }
}
