<?php

declare(strict_types=1);

namespace JSONAPI\OAS;

use JSONAPI\Document\Serializable;

/**
 * Class Discriminator
 *
 * @package JSONAPI\OAS
 */
class Discriminator implements Serializable
{
    /**
     * @var string
     */
    private string $propertyName;
    /**
     * @var string[]
     */
    private array $mapping = [];

    /**
     * Discriminator constructor.
     *
     * @param string $propertyName
     */
    public function __construct(string $propertyName)
    {
        $this->propertyName = $propertyName;
    }

    /**
     * @param string $payloadName
     * @param string $schemaName
     *
     * @return Discriminator
     */
    public function addMapping(string $payloadName, string $schemaName): Discriminator
    {
        $this->mapping[$payloadName] = $schemaName;
        return $this;
    }

    public function jsonSerialize(): object
    {
        $ret = [
            'propertyName' => $this->propertyName
        ];
        if ($this->mapping) {
            $ret['mapping'] = $this->mapping;
        }

        return (object)$ret;
    }
}
