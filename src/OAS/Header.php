<?php

declare(strict_types=1);

namespace JSONAPI\OAS;

use JSONAPI\OAS\Enum\In;
use ReflectionClass;

/**
 * Class Header
 *
 * @package JSONAPI\OAS
 */
class Header extends Parameter
{
    /**
     * Header constructor.
     *
     * @param string $name
     */
    public function __construct(string $name)
    {
        parent::__construct($name, In::HEADER());
    }

    /**
     * @param string $to
     *
     * @param        $origin
     *
     * @return Header
     */
    public static function createReference(string $to, $origin): Header
    {
        /** @var Header $static */
        $static = (new ReflectionClass(__CLASS__))->newInstanceWithoutConstructor();
        $static->setRef($to, $origin);
        return $static;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return parent::getName();
    }

    /**
     * @return object
     * @throws Exception\IncompleteObjectException
     */
    public function jsonSerialize()
    {
        $ret = parent::jsonSerialize();
        unset($ret->name);
        unset($ret->in);
        return (object)$ret;
    }
}
