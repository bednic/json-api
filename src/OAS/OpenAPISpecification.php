<?php

declare(strict_types=1);

namespace JSONAPI\OAS;

/**
 * Class OpenAPI
 *
 * @package JSONAPI\OAS
 */
class OpenAPISpecification implements \JsonSerializable
{

    private const VERSION = '3.0.3';
    /**
     * @var Info
     */
    private Info $info;
    /**
     * @var Server[]
     */
    private array $servers = [];
    /**
     * @var Paths
     */
    private Paths $paths;
    /**
     * @var Components
     */
    private Components $components;
    /**
     * @var SecurityRequirement[]
     */
    private array $security = [];
    /**
     * @var Tag[]
     */
    private array $tags = [];
    /**
     * @var ExternalDocumentation|null
     */
    private ?ExternalDocumentation $externalDocs = null;

    /**
     * OpenAPI constructor.
     *
     * @param Info  $info
     */
    public function __construct(Info $info)
    {
        $this->info  = $info;
        $this->paths = new Paths();
        $this->components = new Components();
    }

    /**
     * @return Info
     */
    public function getInfo(): Info
    {
        return $this->info;
    }

    /**
     * @param Server $server
     *
     * @return OpenAPISpecification
     */
    public function addServer(Server $server): OpenAPISpecification
    {
        $this->servers[] = $server;
        return $this;
    }

    /**
     * @return Paths
     */
    public function getPaths(): Paths
    {
        return $this->paths;
    }

    /**
     * @return Components
     */
    public function getComponents(): Components
    {
        return $this->components;
    }

    /**
     * @param SecurityRequirement $requirement
     *
     * @return OpenAPISpecification
     */
    public function addSecurityRequirement(SecurityRequirement $requirement): OpenAPISpecification
    {
        $this->security[] = $requirement;
        return $this;
    }

    /**
     * @param Tag $tag
     *
     * @return OpenAPISpecification
     */
    public function addTag(Tag $tag): OpenAPISpecification
    {
        $this->tags[] = $tag;
        return $this;
    }

    /**
     * @param ExternalDocumentation $externalDocs
     *
     * @return OpenAPISpecification
     */
    public function setExternalDocs(ExternalDocumentation $externalDocs): OpenAPISpecification
    {
        $this->externalDocs = $externalDocs;
        return $this;
    }


    public function jsonSerialize()
    {
        if (empty($this->servers)) {
            $this->servers[] = new Server('/');
        }
        $ret = [
            'openapi' => self::VERSION,
            'info'    => $this->info,
            'paths'   => $this->paths,
            'servers' => $this->servers
        ];
        if ($this->components) {
            $ret['components'] = $this->components;
        }
        if ($this->security) {
            $ret['security'] = $this->security;
        }
        if ($this->tags) {
            $ret['tags'] = $this->tags;
        }
        if ($this->externalDocs) {
            $ret['externalDocs'] = $this->externalDocs;
        }
        return (object)$ret;
    }
}
