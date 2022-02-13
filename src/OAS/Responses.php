<?php

declare(strict_types=1);

namespace JSONAPI\OAS;

use JSONAPI\Document\Serializable;

/**
 * Class Responses
 *
 * @package JSONAPI\OAS
 */
class Responses implements Serializable
{
    /**
     * @var Response
     */
    private ?Response $default = null;

    /**
     * @var Response[]
     */
    private array $byStatus = [];

    /**
     * @param Response $default
     *
     * @return Responses
     */
    public function setDefault(Response $default): Responses
    {
        $this->default = $default;
        return $this;
    }

    /**
     * @param string   $code
     * @param Response $response
     *
     * @return Responses
     */
    public function addResponse(string $code, Response $response): Responses
    {
        $this->byStatus[$code] = $response;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): object
    {
        $ret = $this->byStatus;
        if ($this->default) {
            $ret['default'] = $this->default;
        }
        return (object)$ret;
    }
}
