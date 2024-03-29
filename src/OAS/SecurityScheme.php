<?php

declare(strict_types=1);

namespace JSONAPI\OAS;

use JSONAPI\Document\Serializable;
use JSONAPI\Exception\OAS\InvalidArgumentException;
use JSONAPI\Exception\OAS\InvalidFormatException;
use JSONAPI\OAS\Type\In;
use JSONAPI\OAS\Type\SecuritySchemeScheme;
use JSONAPI\OAS\Type\SecuritySchemeType;
use ReflectionClass;

/**
 * Class SecurityScheme
 *
 * @package JSONAPI\OAS
 */
class SecurityScheme extends Reference implements Serializable
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
     * @example In::QUERY, In:HEADER, In::COOKIE
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
        if ($in === In::PATH) {
            throw new InvalidArgumentException();
        }
        if (filter_var($openIdConnectUrl, FILTER_VALIDATE_URL, FILTER_NULL_ON_FAILURE) === null) {
            throw new InvalidFormatException();
        }
        $this->type = $type;
        $this->name = $name;
        $this->in = $in;
        $this->scheme = $scheme;
        $this->flows = $flows;
        $this->openIdConnectUrl = $openIdConnectUrl;
    }

    /**
     * @inheritDoc
     */
    public static function createReference(string $to, $origin): SecurityScheme
    {
        /** @var SecurityScheme $static */
        $static = (new ReflectionClass(__CLASS__))->newInstanceWithoutConstructor(); // NOSONAR
        $static->setRef($to, $origin);
        return $static;
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
    public function jsonSerialize(): object
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
}
