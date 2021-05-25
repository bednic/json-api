<?php

declare(strict_types=1);

namespace JSONAPI\Metadata;

/**
 * Class Attribute
 *
 * @package JSONAPI\Metadata
 */
class Attribute extends Field
{
    public ?string $of = null;
    public ?string $type = null;

    /**
     * Attribute constructor.
     *
     * @param string|null $name
     * @param string|null $property
     * @param string|null $getter
     * @param string|null $setter
     * @param string|null $type
     * @param string|null $of
     * @param bool|null   $nullable
     */
    protected function __construct(
        string $name = null,
        string $property = null,
        string $getter = null,
        string $setter = null,
        ?string $type = null,
        ?string $of = null,
        ?bool $nullable = null
    ) {
        parent::__construct($name, $property, $getter, $setter, $nullable);
        $this->type = $type;
        $this->of = $of;
    }


    /**
     * @param string      $property
     * @param string|null $of
     * @param string|null $name
     * @param string|null $type
     * @param bool|null   $nullable
     *
     * @return self
     */
    public static function createByProperty(
        string $property,
        string $of = null,
        string $name = null,
        string $type = null,
        bool $nullable = null
    ): self {
        return new self($name, $property, null, null, $type, $of, $nullable);
    }

    /**
     * @param string      $getter
     * @param string|null $setter
     * @param string|null $name
     * @param string|null $type
     * @param string|null $of
     * @param bool|null   $nullable
     *
     * @return self
     */
    public static function createByMethod(
        string $getter,
        string $setter = null,
        string $name = null,
        string $type = null,
        string $of = null,
        bool $nullable = null
    ): self {
        return new self($name, null, $getter, $setter, $type, $of, $nullable);
    }
}
