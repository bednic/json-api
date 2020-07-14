<?php

declare(strict_types=1);

namespace JSONAPI\OAS;

use JSONAPI\OAS\Enum\In;

/**
 * Class Header
 *
 * @package JSONAPI\OAS
 */
class Header extends Parameter
{
    public function __construct(string $name)
    {
        parent::__construct($name, In::HEADER());
    }

    public function getName(): string
    {
        return parent::getName();
    }

    public function jsonSerialize()
    {
        $ret = parent::jsonSerialize();
        unset($ret->name);
        unset($ret->in);
        return (object)$ret;
    }

    /**
     * @param string $to
     *
     * @return Header
     */
    public static function createReference(string $to): Header
    {
        /** @var Header $static */
        $static = (new \ReflectionClass(__CLASS__))->newInstanceWithoutConstructor();
        $static->setRef($to);
        return $static;
    }
}
