<?php

declare(strict_types=1);

namespace JSONAPI\OAS;

use JSONAPI\OAS\Exception\InvalidFormatException;
use JSONAPI\Document\Serializable;

/**
 * Class Contact
 *
 * @package JSONAPI\OAS
 */
class Contact implements Serializable
{
    /**
     * @var string|null
     */
    private ?string $name = null;
    /**
     * @var string|null
     */
    private ?string $url = null;
    /**
     * @var string|null
     */
    private ?string $email = null;

    /**
     * @param string|null $name
     *
     * @return Contact
     */
    public function setName(?string $name): Contact
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param string|null $url
     *
     * @return Contact
     * @throws InvalidFormatException
     */
    public function setUrl(?string $url): Contact
    {
        if (filter_var($url, FILTER_VALIDATE_URL, FILTER_NULL_ON_FAILURE) === null) {
            throw new InvalidFormatException();
        }
        $this->url = $url;
        return $this;
    }

    /**
     * @param string|null $email
     *
     * @return Contact
     * @throws InvalidFormatException
     */
    public function setEmail(?string $email): Contact
    {
        if (filter_var($email, FILTER_VALIDATE_EMAIL, FILTER_NULL_ON_FAILURE) === null) {
            throw new InvalidFormatException();
        }
        $this->email = $email;
        return $this;
    }

    public function jsonSerialize()
    {
        return [
            'name'  => $this->name,
            'url'   => $this->url,
            'email' => $this->email
        ];
    }
}
