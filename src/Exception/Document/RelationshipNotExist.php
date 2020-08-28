<?php

declare(strict_types=1);

namespace JSONAPI\Exception\Document;

use JSONAPI\Exception\HasPointer;

/**
 * Class RelationshipNotExist
 *
 * @package JSONAPI\Exception\Document
 */
class RelationshipNotExist extends FieldNotExist implements HasPointer
{
    public function getPointer(): string
    {
        return '/data/relationships/' . $this->field;
    }
}
