<?php

declare(strict_types=1);

namespace JSONAPI\Document;

use JSONAPI\Exception\Document\ForbiddenCharacter;
use JSONAPI\Exception\Document\ForbiddenDataType;

/**
 * Class Id
 *
 * @package JSONAPI\Document
 */
final class Id extends Field
{
    /**
     * Id constructor.
     *
     * @param string|null $id
     *
     * @throws ForbiddenDataType
     * @throws ForbiddenCharacter
     */
    public function __construct(?string $id)
    {
        parent::__construct('id');
        $this->setData($id);
    }

    /**
     * @param string $id
     *
     * @throws ForbiddenDataType
     */
    protected function setData($id): void
    {
        if (is_string($id) || is_null($id)) {
            $this->data = $id;
        } else {
            throw new ForbiddenDataType(gettype($id));
        }
    }
}
