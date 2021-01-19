<?php

/**
 * Created by tomas.benedikt@gmail.com
 */

declare(strict_types=1);

namespace JSONAPI\Annotation;

/**
 * Class Meta
 *
 * @package JSONAPI\Annotation
 */
#[\Attribute]
final class Meta extends \JSONAPI\Metadata\Meta
{
    /**
     * @inheritDoc
     */
    public function __construct(string $getter)
    {
        parent::__construct($getter);
    }
}
