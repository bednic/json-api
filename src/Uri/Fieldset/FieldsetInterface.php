<?php

declare(strict_types=1);

namespace JSONAPI\Uri\Fieldset;

use JSONAPI\Uri\QueryPartInterface;

/**
 * Interface FieldsetInterface
 *
 * @package JSONAPI\Uri\Fieldset
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
