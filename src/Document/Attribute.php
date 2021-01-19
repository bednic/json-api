<?php

declare(strict_types=1);

namespace JSONAPI\Document;

use JSONAPI\Exception\Document\ForbiddenCharacter;

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
     */
    public function __construct(string $key, mixed $data)
    {
        parent::__construct($key);
        $this->setData($data);
    }
}
