<?php

declare(strict_types=1);

namespace JSONAPI\OAS\Type;

use JSONAPI\OAS\Schema;

/**
 * Class DataType
 *
 * @package JSONAPI\OAS\Factory
 */
class DataType
{
    /**
     * signed 32 bits
     *
     * @return Schema
     */
    public static function int32(): Schema
    {
        return (new Schema())->setType('integer')->setFormat('int32');
    }

    /**
     * signed 64 bits (a.k.a long)
     *
     * @return Schema
     */
    public static function int64(): Schema
    {
        return (new Schema())->setType('integer')->setFormat('int64');
    }

    /**
     * @return Schema
     */
    public static function float(): Schema
    {
        return (new Schema())->setType('number')->setFormat('float');
    }

    /**
     * @return Schema
     */
    public static function double(): Schema
    {
        return (new Schema())->setType('number')->setFormat('double');
    }

    /**
     * @return Schema
     */
    public static function string(): Schema
    {
        return (new Schema())->setType('string');
    }

    /**
     * base64 encoded characters
     *
     * @return Schema
     */
    public static function byte(): Schema
    {
        return (new Schema())->setType('string')->setFormat('byte');
    }

    /**
     * any sequence of octets
     *
     * @return Schema
     */
    public static function binary(): Schema
    {
        return (new Schema())->setType('string')->setFormat('binary');
    }

    /**
     * @return Schema
     */
    public static function boolean(): Schema
    {
        return (new Schema())->setType('boolean');
    }

    /**
     * As defined by full-date - RFC3339
     *
     * @return Schema
     */
    public static function date(): Schema
    {
        return (new Schema())->setType('string')->setFormat('date');
    }

    /**
     * As defined by date-time - RFC3339
     *
     * @return Schema
     */
    public static function dateTime(): Schema
    {
        return (new Schema())->setType('string')->setFormat('date-time');
    }

    /**
     * A hint to UIs to obscure input.
     *
     * @return Schema
     */
    public static function password(): Schema
    {
        return (new Schema())->setType('string')->setFormat('password');
    }

    /**
     * Custom shortcut for object schema
     *
     * @return Schema
     */
    public static function object(): Schema
    {
        return (new Schema())->setType('object');
    }

    /**
     * @param Schema|null $items
     *
     * @return Schema
     */
    public static function array(?Schema $items): Schema
    {
        $schema = (new Schema())->setType('array');
        if ($items) {
            $schema->setItems($items);
        }
        return $schema;
    }
}
