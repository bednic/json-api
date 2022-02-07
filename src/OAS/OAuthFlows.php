<?php

declare(strict_types=1);

namespace JSONAPI\OAS;

use JSONAPI\Document\Serializable;

/**
 * Class OAuthFlows
 *
 * @package JSONAPI\OAS
 */
class OAuthFlows implements Serializable
{
    private ?OAuthFlow $implicit = null;
    private ?OAuthFlow $password = null;
    private ?OAuthFlow $clientCredentials = null;
    private ?OAuthFlow $authorizationCode = null;

    /**
     * @param OAuthFlow $implicit
     *
     * @return OAuthFlows
     */
    public function setImplicit(OAuthFlow $implicit): OAuthFlows
    {
        $this->implicit = $implicit;
        return $this;
    }

    /**
     * @param OAuthFlow $password
     *
     * @return OAuthFlows
     */
    public function setPassword(OAuthFlow $password): OAuthFlows
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @param OAuthFlow $clientCredentials
     *
     * @return OAuthFlows
     */
    public function setClientCredentials(OAuthFlow $clientCredentials): OAuthFlows
    {
        $this->clientCredentials = $clientCredentials;
        return $this;
    }

    /**
     * @param OAuthFlow $authorizationCode
     *
     * @return OAuthFlows
     */
    public function setAuthorizationCode(OAuthFlow $authorizationCode): OAuthFlows
    {
        $this->authorizationCode = $authorizationCode;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        $ret = [];
        if ($this->implicit) {
            $ret['implicit'] = $this->implicit;
        }
        if ($this->password) {
            $ret['password'] = $this->password;
        }
        if ($this->clientCredentials) {
            $ret['clientCredentials'] = $this->clientCredentials;
        }
        if ($this->authorizationCode) {
            $ret['authorizationCode'] = $this->authorizationCode;
        }
        return (object)$ret;
    }
}
