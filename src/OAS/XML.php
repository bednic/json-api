<?php

declare(strict_types=1);

namespace JSONAPI\OAS;

use JSONAPI\Document\Serializable;

/**
 * Class XML
 *
 * @package JSONAPI\OAS
 */
class XML implements Serializable
{
    /**
     * @var string|null
     */
    private ?string $name;
    /**
     * @var string|null
     */
    private ?string $namespace;
    /**
     * @var string|null
     */
    private ?string $prefix;
    /**
     * @var bool
     */
    private bool $attribute = false;
    /**
     * @var bool
     */
    private bool $wrapped = false;

    /**
     * @param string|null $name
     *
     * @return XML
     */
    public function setName(?string $name): XML
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param string|null $namespace
     *
     * @return XML
     */
    public function setNamespace(?string $namespace): XML
    {
        $this->namespace = $namespace;
        return $this;
    }

    /**
     * @param string|null $prefix
     *
     * @return XML
     */
    public function setPrefix(?string $prefix): XML
    {
        $this->prefix = $prefix;
        return $this;
    }

    /**
     * @param bool $attribute
     *
     * @return XML
     */
    public function setAttribute(bool $attribute): XML
    {
        $this->attribute = $attribute;
        return $this;
    }

    /**
     * @param bool $wrapped
     *
     * @return XML
     */
    public function setWrapped(bool $wrapped): XML
    {
        $this->wrapped = $wrapped;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): object
    {
        $ret = [
            'attribute' => $this->attribute,
            'wrapped'   => $this->wrapped
        ];
        if ($this->name) {
            $ret['name'] = $this->name;
        }
        if ($this->namespace) {
            $ret['namespace'] = $this->namespace;
        }
        if ($this->prefix) {
            $ret['prefix'] = $this->prefix;
        }
        return (object)$ret;
    }
}
