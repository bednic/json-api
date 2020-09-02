<?php

declare(strict_types=1);

namespace JSONAPI\OAS;

use JSONAPI\OAS\Exception\InvalidFormatException;
use JSONAPI\Document\Serializable;

/**
 * Class Paths
 *
 * @package JSONAPI\OAS
 */
class Paths implements Serializable
{
    /**
     * @var PathItem[]
     */
    private array $items = [];

    /**
     * @param string   $pattern
     * @param PathItem $item
     *
     * @return Paths
     * @throws InvalidFormatException
     */
    public function addPath(string $pattern, PathItem $item): Paths
    {
        if (!preg_match('/^\/.*/', $pattern)) {
            throw new InvalidFormatException();
        }
        $this->items[$pattern] = $item;
        return $this;
    }

    public function jsonSerialize()
    {
        return (object)$this->items;
    }
}
