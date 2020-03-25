<?php

declare(strict_types=1);

namespace JSONAPI\Document;

use JSONAPI\Exception\Document\ForbiddenDataType;

/**
 * Class Id
 *
 * @package JSONAPI\Document
 */
final class Id extends Field
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
        if (is_string($id) || is_null($id)) {
            $this->data = $id;
        } else {
            throw new ForbiddenDataType(gettype($id));
        }
    }
}