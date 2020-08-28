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
     */
    public function __construct(string $type)
    {
        parent::__construct('type');
        $this->setData($type);
    }

    /**
     * @param string $type
     */
    protected function setData($type): void
    {
        $this->data = $type;
    }
}
