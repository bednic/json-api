<?php

declare(strict_types=1);

namespace JSONAPI\OAS;

use JSONAPI\Document\Serializable;

/**
 * Class Tag
 * Adds metadata to a single tag that is used by the Operation Object.
 * It is not mandatory to have a Tag Object per tag defined in the Operation Object instances.
 *
 * @see     \JSONAPI\OAS\Operation Operation Object
 * @package JSONAPI\OAS
 */
class Tag implements Serializable
{
    /**
     * @var string
     */
    private string $name;
    /**
     * @var string|null
     */
    private ?string $description = null;
    /**
     * @var ExternalDocumentation|null
     */
    private ?ExternalDocumentation $externalDocs = null;

    /**
     * Tag constructor.
     *
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * @param string|null $description
     *
     * @return Tag
     */
    public function setDescription(?string $description): Tag
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @param ExternalDocumentation|null $externalDocs
     *
     * @return Tag
     */
    public function setExternalDocs(?ExternalDocumentation $externalDocs): Tag
    {
        $this->externalDocs = $externalDocs;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): object
    {
        $ret = [
            'name' => $this->name
        ];
        if ($this->description) {
            $ret['description'] = $this->description;
        }
        if ($this->externalDocs) {
            $ret['externalDocs'] = $this->externalDocs;
        }
        return (object)$ret;
    }
}
