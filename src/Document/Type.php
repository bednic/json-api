<?php

namespace JSONAPI\Document;

use JSONAPI\Exception\Document\ForbiddenCharacter;
use JSONAPI\Exception\Document\ForbiddenDataType;

/**
 * Class Type
 *
 * @package JSONAPI\Document
 */
class Type extends Field
{
    /**
     * Type constructor.
     *
     * @param string $type
     *
     * @throws ForbiddenCharacter
     * @throws ForbiddenDataType
     */
    public function __construct(string $type)
    {
        parent::__construct('type', $type);
    }

    /**
     * @param string $type
     */
    public function setData($type): void
    {
        $this->data = $type;
    }
}
