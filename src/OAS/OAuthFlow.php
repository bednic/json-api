<?php

declare(strict_types=1);

namespace JSONAPI\OAS;

use JSONAPI\Document\Serializable;

/**
 * Class OAuthFlow
 *
 * @package JSONAPI\OAS
 */
class OAuthFlow implements Serializable
{

    /**
     * @var string
     */
    private string $authorizationUrl;
    /**
     * @var string
     */
    private string $tokenUrl;
    /**
     * @var string|null
     */
    private ?string $refreshUrl = null;
    /**
     * @var string[]
     */
    private array $scopes;

    /**
     * OAuthFlow constructor.
     *
     * @param string $authorizationUrl
     * @param string $tokenUrl
     * @param array  $scopes
     */
    public function __construct(string $authorizationUrl, string $tokenUrl, array $scopes = [])
    {
        $this->authorizationUrl = $authorizationUrl;
        $this->tokenUrl = $tokenUrl;
        $this->scopes = $scopes;
    }

    /**
     * @param string|null $refreshUrl
     *
     * @return OAuthFlow
     */
    public function setRefreshUrl(?string $refreshUrl): OAuthFlow
    {
        $this->refreshUrl = $refreshUrl;
        return $this;
    }

    /**
     * @param string $name
     * @param string $description
     *
     * @return OAuthFlow
     */
    public function addScope(string $name, string $description): OAuthFlow
    {
        $this->scopes[$name] = $description;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        $ret = [
            'authorizationUrl' => $this->authorizationUrl,
            'tokenUrl'         => $this->tokenUrl,
            'scopes'           => $this->scopes
        ];

        if ($this->refreshUrl) {
            $ret['refreshUrl'] = $this->refreshUrl;
        }
        return (object)$ret;
    }
}
