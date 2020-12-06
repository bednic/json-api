<?php

declare(strict_types=1);

namespace JSONAPI\Annotation;

use JSONAPI\Metadata\Meta;

/**
 * Class Relationship
 *
 * @package JSONAPI\Annotation
 */

#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::TARGET_PROPERTY)]
final class Relationship extends \JSONAPI\Metadata\Relationship
{
    /**
     * @var Meta|null
     */
    public ?Meta $meta = null;

    /**
     * @inheritDoc
     */
    public function __construct(
        ?string $target,
        string $name = null,
        string $property = null,
        string $getter = null,
        string $setter = null,
        ?bool $isCollection = null
    ) {
        parent::__construct($name, $property, $getter, $setter);
        $this->target       = $target;
        $this->isCollection = $isCollection;
    }
}
