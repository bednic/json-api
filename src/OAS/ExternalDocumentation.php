<?php

declare(strict_types=1);

namespace JSONAPI\OAS;

use JSONAPI\OAS\Exception\InvalidFormatException;

/**
 * Class ExternalDocumentation
 *
 * @package JSONAPI\OAS
 */
class ExternalDocumentation implements \JsonSerializable
{

    /**
     * @var string|null
     */
    private ?string $description = null;
    /**
     * @var string
     */
    private string $url;

    /**
     * ExternalDocumentation constructor.
     *
     * @param string $url
     *
     * @throws InvalidFormatException
     */
    public function __construct(string $url)
    {
        if (filter_var($url, FILTER_VALIDATE_URL, FILTER_NULL_ON_FAILURE) === null) {
            throw new InvalidFormatException();
        }
        $this->url = $url;
    }

    /**
     * @param string|null $description
     */
    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function jsonSerialize()
    {
        $ret = ['url' => $this->url];
        if ($this->description) {
            $ret['description'] = $this->description;
        }
        return (object)$ret;
    }
}
