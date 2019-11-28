<?php


namespace JSONAPI\Uri;


interface SparseFieldset
{
    public function showField(string $type, string $fieldName): bool;
}
