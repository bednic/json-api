<?php

declare(strict_types=1);

namespace JSONAPI\OAS;

use JSONAPI\Document\Serializable;

/**
 * Class Server
 *
 * @package JSONAPI\OAS
 */
class Server implements Serializable
{
    /**
     * @var string
     */
    private string $url;

    /**
     * @var string|null
     */
    private ?string $description = null;
    /**
     * @var ServerVariable[]
     */
    private array $variables = [];

    /**
     * Server constructor.
     *
     * @param string $url
     */
    public function __construct(string $url)
    {
        $this->url = $url;
    }

    /**
     * @param string         $name
     * @param ServerVariable $variable
     */
    public function addVariable(string $name, ServerVariable $variable)
    {
        $this->variables[$name] = $variable;
    }

    /**
     * @param string|null $description
     */
    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        $ret = [
            'url' => $this->url
        ];
        if ($this->description) {
            $ret['description'] = $this->description;
        }
        if ($this->variables) {
            $ret['variables'] = $this->variables;
        }
        return (object)$ret;
    }
}
