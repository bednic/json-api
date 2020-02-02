<?php

/**
 * Created by IntelliJ IDEA.
 * User: tomas
 * Date: 01.02.2019
 * Time: 0:41
 */

namespace JSONAPI\Metadata;

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
     * @var \JSONAPI\Metadata\Meta
     */
    public ?Meta $meta = null;

    /**
     * @param string    $type
     * @param bool      $readOnly
     * @param Meta|null $meta
     *
     * @return static
     */
    public static function create(string $type, bool $readOnly = false, Meta $meta = null): self
    {
        $self = new static();
        $self->type = $type;
        $self->readOnly = $readOnly;
        $self->meta = $meta;
        return $self;
    }
}
