<?php

namespace JSONAPI\Schema;

use JSONAPI\Metadata\ClassMetadata;
use JSONAPI\Metadata\Id;
use JSONAPI\Metadata\Meta;

/**
 * Interface ResourceDescriptor
 *
 * @package JSONAPI\Schema
 */
interface Schema
{
    public static function getClassName();

    public static function getId(): Id;

    public static function getType(): string;

    public static function isReadOnly(): bool;

    public static function getResourceMeta(): ?Meta;

    public static function getAttributes(): iterable;

    public static function getRelationships(): iterable;
}
