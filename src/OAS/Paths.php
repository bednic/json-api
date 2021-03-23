<?php

declare(strict_types=1);

namespace JSONAPI\OAS;

use JSONAPI\Document\Serializable;
use JSONAPI\Exception\OAS\InvalidFormatException;

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

    /**
     * @param string $pattern
     * @return bool
     */
    public function exists(string $pattern): bool
    {
        return isset($this->items[$pattern]);
    }

    /**
     * @param string $pattern
     * @return PathItem|null
     */
    public function getPath(string $pattern): ?PathItem
    {
        return $this->items[$pattern] ?? null;
    }

    public function jsonSerialize()
    {
        return (object)$this->items;
    }
}
