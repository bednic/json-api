<?php

namespace JSONAPI\Uri\Fieldset;

use JSONAPI\Uri\UriPartInterface;

/**
 * Interface FieldsetInterface
 *
 * @package JSONAPI\Uri\Fieldset
 */
interface FieldsetInterface extends UriPartInterface
{
    /**
     * @param string $type
     * @param string $fieldName
     *
     * @return bool
     */
    public function showField(string $type, string $fieldName): bool;
}
