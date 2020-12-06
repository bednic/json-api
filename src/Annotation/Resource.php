<?php

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

    /**
     * Resource constructor.
     *
     * @param string|null $type
     * @param bool        $readOnly
     */
    public function __construct(
        public ?string $type = null,
        public bool $readOnly = false
    ) {
    }


}
