<?php

declare(strict_types=1);

namespace JSONAPI\OAS;

use JSONAPI\Document\Serializable;

/**
 * Class PathItem
 *
 * @package JSONAPI\OAS
 */
class PathItem implements Serializable
{
    /**
     * @var string|null
     */
    private ?string $ref = null;
    /**
     * @var string|null
     */
    private ?string $summary = null;
    /**
     * @var string|null
     */
    private ?string $description = null;
    /**
     * @var Operation|null
     */
    private ?Operation $get = null;
    /**
     * @var Operation|null
     */
    private ?Operation $put = null;
    /**
     * @var Operation|null
     */
    private ?Operation $post = null;
    /**
     * @var Operation|null
     */
    private ?Operation $delete = null;
    /**
     * @var Operation|null
     */
    private ?Operation $options = null;
    /**
     * @var Operation|null
     */
    private ?Operation $head = null;
    /**
     * @var Operation|null
     */
    private ?Operation $patch = null;
    /**
     * @var Operation|null
     */
    private ?Operation $trace = null;
    /**
     * @var Server[]|null
     */
    private ?array $servers = null;
    /**
     * @var Parameter[]
     */
    private array $parameters = [];

    /**
     * @param string|null $ref
     *
     * @return PathItem
     */
    public function setRef(?string $ref): PathItem
    {
        $this->ref = $ref;
        return $this;
    }

    /**
     * @param string|null $summary
     *
     * @return PathItem
     */
    public function setSummary(?string $summary): PathItem
    {
        $this->summary = $summary;
        return $this;
    }

    /**
     * @param string|null $description
     *
     * @return PathItem
     */
    public function setDescription(?string $description): PathItem
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @param Operation|null $get
     *
     * @return PathItem
     */
    public function setGet(?Operation $get): PathItem
    {
        $this->get = $get;
        return $this;
    }

    /**
     * @param Operation|null $put
     *
     * @return PathItem
     */
    public function setPut(?Operation $put): PathItem
    {
        $this->put = $put;
        return $this;
    }

    /**
     * @param Operation|null $post
     *
     * @return PathItem
     */
    public function setPost(?Operation $post): PathItem
    {
        $this->post = $post;
        return $this;
    }

    /**
     * @param Operation|null $delete
     *
     * @return PathItem
     */
    public function setDelete(?Operation $delete): PathItem
    {
        $this->delete = $delete;
        return $this;
    }

    /**
     * @param Operation|null $options
     *
     * @return PathItem
     */
    public function setOptions(?Operation $options): PathItem
    {
        $this->options = $options;
        return $this;
    }

    /**
     * @param Operation|null $head
     *
     * @return PathItem
     */
    public function setHead(?Operation $head): PathItem
    {
        $this->head = $head;
        return $this;
    }

    /**
     * @param Operation|null $patch
     *
     * @return PathItem
     */
    public function setPatch(?Operation $patch): PathItem
    {
        $this->patch = $patch;
        return $this;
    }

    /**
     * @param Operation|null $trace
     *
     * @return PathItem
     */
    public function setTrace(?Operation $trace): PathItem
    {
        $this->trace = $trace;
        return $this;
    }

    /**
     * @param Server[]|null $servers
     *
     * @return PathItem
     */
    public function setServers(?array $servers): PathItem
    {
        $this->servers = $servers;
        return $this;
    }

    /**
     * @param Parameter $parameter
     *
     * @return PathItem
     */
    public function addParameter(Parameter $parameter): PathItem
    {
        $this->parameters[] = $parameter;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        $ret = [];
        if ($this->ref) {
            $ret['$ref'] = $this->ref;
        }
        if ($this->summary) {
            $ret['summary'] = $this->summary;
        }
        if ($this->description) {
            $ret['description'] = $this->description;
        }
        if ($this->get) {
            $ret['get'] = $this->get;
        }
        if ($this->put) {
            $ret['put'] = $this->put;
        }
        if ($this->post) {
            $ret['post'] = $this->post;
        }
        if ($this->delete) {
            $ret['delete'] = $this->delete;
        }
        if ($this->options) {
            $ret['options'] = $this->options;
        }
        if ($this->head) {
            $ret['head'] = $this->head;
        }
        if ($this->patch) {
            $ret['patch'] = $this->patch;
        }
        if ($this->trace) {
            $ret['trace'] = $this->trace;
        }
        if ($this->servers) {
            $ret['servers'] = $this->servers;
        }
        if ($this->parameters) {
            $ret['parameters'] = $this->parameters;
        }
        return (object)$ret;
    }
}
