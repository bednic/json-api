<?php

declare(strict_types=1);

namespace JSONAPI\Document;

use JSONAPI\Exception\Document\ForbiddenCharacter;
use JSONAPI\Exception\Document\ForbiddenDataType;

/**
 * Class Type
 *
 * @package JSONAPI\Document
 */
final class Type extends Field
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
        parent::__construct('type');
        $this->setData($type);
    }

    /**
     * @param string $data
     *
     * @throws ForbiddenDataType
     */
    protected function setData(mixed $data): void
    {
        if (is_string($data)) {
            $this->data = $data;
        } else {
            throw new ForbiddenDataType($this->getKey(), gettype($data));
        }
    }
}
