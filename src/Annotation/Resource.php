<?php

declare(strict_types=1);

namespace JSONAPI\Annotation;

/**
 * Class ResourceMetadata
 *
 * @package JSONAPI\Annotation
 * @Annotation
 * @Target({"CLASS"})
 */
final class Resource
{
    /**
     * @var string
     * @Required
     */
    public string $type;

    /**
     * @var bool
     */
    public bool $readOnly = false;

    /**
     * @var \JSONAPI\Annotation\Meta
     */
    public ?Meta $meta = null;

    public function __construct()
    {
        // Override parent constructor cause Doctrine Annotations need public constructor
    }
}
