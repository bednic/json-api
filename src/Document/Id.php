<?php

namespace JSONAPI\Document;

use JSONAPI\Exception\Document\ForbiddenDataType;

/**
 * Class Id
 *
 * @package JSONAPI\Document
 */
class Id extends Field
{

    public function __construct(?string $id)
    {
        parent::__construct('id', $id);
    }

    /**
     * @param string $id
     *
     * @throws ForbiddenDataType
     */
    public function setData($id): void
    {
        if (!is_string($id)) {
            throw new ForbiddenDataType(gettype($id));
        }
        $this->data = $id;
    }
}
