<?php

declare(strict_types=1);

namespace JSONAPI\OAS;

use JSONAPI\OAS\Enum\In;
use JSONAPI\OAS\Enum\SecuritySchemeScheme;
use JSONAPI\OAS\Enum\SecuritySchemeType;
use JSONAPI\OAS\Exception\InvalidArgumentException;
use JSONAPI\OAS\Exception\InvalidFormatException;

/**
 * Class SecurityScheme
 *
 * @package JSONAPI\OAS
 */
class SecurityScheme extends Reference implements \JsonSerializable
{
    /**
     * @var SecuritySchemeType
     */
    private SecuritySchemeType $type;
    /**
     * @var string|null
     */
    private ?string $description = null;
    /**
     * @var string
     */
    private string $name;
    /**
     * @var In
     * @example In::QUERY(), In:HEADER(), In::COOKIE()
     */
    private In $in;
    /**
     * @var SecuritySchemeScheme
     */
    private SecuritySchemeScheme $scheme;
    /**
     * @example 'JWT'
     * @var string|null
     */
    private ?string $bearerFormat = null;
    /**
     * @var OAuthFlows
     */
    private OAuthFlows $flows;
    /**
     * @var string
     */
    private string $openIdConnectUrl;

    /**
     * SecurityScheme constructor.
     *
     * @param SecuritySchemeType   $type
     * @param string               $name
     * @param In                   $in
     * @param SecuritySchemeScheme $scheme
     * @param OAuthFlows           $flows
     * @param string               $openIdConnectUrl
     *
     * @throws InvalidArgumentException
     * @throws InvalidFormatException
     */
    public function __construct(
        SecuritySchemeType $type,
        string $name,
        In $in,
        SecuritySchemeScheme $scheme,
        OAuthFlows $flows,
        string $openIdConnectUrl
    ) {
        if ($in->equals(In::PATH())) {
            throw new InvalidArgumentException();
        }
        if (filter_var($openIdConnectUrl, FILTER_VALIDATE_URL, FILTER_NULL_ON_FAILURE) === null) {
            throw new InvalidFormatException();
        }
        $this->type             = $type;
        $this->name             = $name;
        $this->in               = $in;
        $this->scheme           = $scheme;
        $this->flows            = $flows;
        $this->openIdConnectUrl = $openIdConnectUrl;
    }

    /**
     * @param string $description
     *
     * @return SecurityScheme
     */
    public function setDescription(string $description): SecurityScheme
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @param string $bearerFormat
     *
     * @return SecurityScheme
     */
    public function setBearerFormat(string $bearerFormat): SecurityScheme
    {
        $this->bearerFormat = $bearerFormat;
        return $this;
    }


    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        if ($this->isReference()) {
            return parent::jsonSerialize();
        }
        $ret = [
            'type'             => $this->type,
            'name'             => $this->name,
            'in'               => $this->in,
            'scheme'           => $this->scheme,
            'flows'            => $this->flows,
            'openIdConnectUrl' => $this->openIdConnectUrl,
        ];
        if ($this->description) {
            $ret['description'] = $this->description;
        }
        if ($this->bearerFormat) {
            $ret['bearerFormat'] = $this->bearerFormat;
        }
        return (object)$ret;
    }

    /**
     * @inheritDoc
     */
    public static function createReference(string $to): SecurityScheme
    {
        /** @var SecurityScheme $static */
        $static = (new \ReflectionClass(__CLASS__))->newInstanceWithoutConstructor();
        $static->setRef($to);
        return $static;
    }
}
