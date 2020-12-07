<?php

declare(strict_types=1);

namespace JSONAPI\Document;

use JSONAPI\Exception\Document\ForbiddenCharacter;
use JSONAPI\Exception\Document\ForbiddenDataType;

/**
 * Class AttributeMetadata
 *
 * @package JSONAPI\Document
 */
final class Attribute extends Field
{
    /**
     * Attribute constructor.
     *
     * @param string $key
     * @param mixed  $data
     *
     * @throws ForbiddenCharacter
     * @throws ForbiddenDataType
     */
    public function __construct(string $key, mixed $data)
    {
        parent::__construct($key);
        $this->setData($data);
    }
}
