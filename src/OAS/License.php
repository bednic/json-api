<?php

declare(strict_types=1);

namespace JSONAPI\OAS;

use JSONAPI\OAS\Exception\InvalidFormatException;
use Tools\JSON\JsonSerializable;

/**
 * Class Licence
 *
 * @package JSONAPI\OAS
 */
class License implements JsonSerializable
{
    /**
     * @var string
     */
    private string $name;
    /**
     * @var string|null
     */
    private ?string $url = null;

    /**
     * Licence constructor.
     *
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * @param string|null $url
     *
     * @return License
     * @throws InvalidFormatException
     */
    public function setUrl(?string $url): License
    {
        if (filter_var($url, FILTER_VALIDATE_URL, FILTER_NULL_ON_FAILURE) === null) {
            throw new InvalidFormatException();
        }
        $this->url = $url;
        return $this;
    }

    public function jsonSerialize()
    {
        $ret = ['name' => $this->name];
        if ($this->url) {
            $ret['url'] = $this->url;
        }
        return (object)$ret;
    }
}
