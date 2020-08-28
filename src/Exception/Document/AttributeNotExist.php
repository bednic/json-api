<?php

declare(strict_types=1);

namespace JSONAPI\Exception\Document;

use JSONAPI\Exception\HasPointer;

/**
 * Class AttributeNotExist
 *
 * @package JSONAPI\Exception\Document
 */
class AttributeNotExist extends FieldNotExist implements HasPointer
{

    public function getPointer(): string
    {
        return '/data/attributes/' . $this->field;
    }
}
