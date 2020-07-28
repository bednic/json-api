<?php

declare(strict_types=1);

namespace JSONAPI\OAS;

use JSONAPI\OAS\Exception\InvalidFormatException;
use Tools\JSON\JsonSerializable;

/**
 * Class Info
 *
 * @package JSONAPI\OAS
 */
class Info implements JsonSerializable
{
    /**
     * @var string
     */
    private string $title;
    /**
     * @var string|null
     */
    private ?string $description = null;
    /**
     * @var string|null
     */
    private ?string $termsOfService = null;
    /**
     * @var Contact|null
     */
    private ?Contact $contact = null;
    /**
     * @var License|null
     */
    private ?License $license = null;
    /**
     * @var string
     */
    private string $version;

    /**
     * Info constructor.
     *
     * @param string $title
     * @param string $version
     */
    public function __construct(
        string $title,
        string $version
    ) {
        $this->title   = $title;
        $this->version = $version;
    }

    /**
     * @param string|null $description
     *
     * @return Info
     */
    public function setDescription(?string $description): Info
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @param string $url
     *
     * @return Info
     * @throws InvalidFormatException
     */
    public function setTermsOfService(string $url): Info
    {
        if (filter_var($url, FILTER_VALIDATE_URL, FILTER_NULL_ON_FAILURE) === null) {
            throw new InvalidFormatException();
        }
        $this->termsOfService = $url;
        return $this;
    }

    /**
     * @param Contact $contact
     *
     * @return Info
     */
    public function setContact(Contact $contact): Info
    {
        $this->contact = $contact;
        return $this;
    }

    /**
     * @param License $license
     *
     * @return Info
     */
    public function setLicense(License $license): Info
    {
        $this->license = $license;
        return $this;
    }

    public function jsonSerialize()
    {
        $ret = [
            'title'   => $this->title,
            'version' => $this->version
        ];
        if ($this->description) {
            $ret['description'] = $this->description;
        }
        if ($this->termsOfService) {
            $ret['termsOfService'] = $this->termsOfService;
        }
        if ($this->contact) {
            $ret['contact'] = $this->contact;
        }
        if ($this->license) {
            $ret['license'] = $this->license;
        }
        return (object)$ret;
    }
}
