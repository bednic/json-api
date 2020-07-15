<?php

declare(strict_types=1);

namespace JSONAPI\OAS;

use Fig\Http\Message\StatusCodeInterface;

/**
 * Class Responses
 *
 * @package JSONAPI\OAS
 */
class Responses implements \JsonSerializable
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
    public function jsonSerialize()
    {
        $ret = $this->byStatus;
        if ($this->default) {
            $ret['default'] = $this->default;
        }
        return (object)$ret;
    }
}
