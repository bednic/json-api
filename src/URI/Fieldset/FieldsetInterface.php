<?php

declare(strict_types=1);

namespace JSONAPI\URI\Fieldset;

use JSONAPI\URI\QueryPartInterface;

/**
 * Interface FieldsetInterface
 *
 * @package JSONAPI\URI\Fieldset
 */
interface FieldsetInterface extends QueryPartInterface
{
    /**
     * @param string $type
     * @param string $fieldName
     *
     * @return bool
     */
    public function showField(string $type, string $fieldName): bool;
}
