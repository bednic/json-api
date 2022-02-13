<?php

declare(strict_types=1);

namespace JSONAPI\OAS;

use JSONAPI\Exception\OAS\IncompleteObjectException;
use JSONAPI\Exception\OAS\OpenAPIException;
use JSONAPI\OAS\Type\In;
use ReflectionClass;
use ReflectionException;

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
        parent::__construct($name, In::HEADER);
    }

    /**
     * @param string                                                                            $to
     * @param SecurityScheme|Schema|Response|RequestBody|Parameter|Header|Link|Example|Callback $origin
     *
     * @return Header
     * @throws OpenAPIException
     */
    public static function createReference(string $to, $origin): Header
    {
        try {
            /** @var Header $static */
            $static = (new ReflectionClass(__CLASS__))->newInstanceWithoutConstructor(); //NOSONAR
            $static->setRef($to, $origin);
            return $static;
        } catch (ReflectionException $e) {
            throw OpenAPIException::createFromPrevious($e);
        }
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
     * @throws IncompleteObjectException
     */
    public function jsonSerialize(): object
    {
        $ret = parent::jsonSerialize();
        unset($ret->name);
        unset($ret->in);
        return $ret;
    }
}
