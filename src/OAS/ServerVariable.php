<?php

declare(strict_types=1);

namespace JSONAPI\OAS;

use JSONAPI\Document\Serializable;

/**
 * Class ServerVariable
 *
 * @package JSONAPI\OAS
 */
class ServerVariable implements Serializable
{
    /**
     * @var string[]
     */
    private array $enum = [];
    /**
     * @var string
     */
    private string $default;
    /**
     * @var string|null
     */
    private ?string $description = null;

    /**
     * ServerVariable constructor.
     *
     * @param string $default
     */
    public function __construct(string $default)
    {
        $this->default = $default;
    }

    /**
     * @param string[] $enum
     */
    public function setEnum(array $enum): void
    {
        $this->enum = $enum;
    }

    /**
     * @param string|null $description
     *
     * @return ServerVariable
     */
    public function setDescription(?string $description): ServerVariable
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): object
    {
        $ret = [
            'default' => $this->default
        ];
        if ($this->enum) {
            $ret['enum'] = $this->enum;
        }
        if ($this->description) {
            $ret['description'] = $this->description;
        }
        return (object)$ret;
    }
}
