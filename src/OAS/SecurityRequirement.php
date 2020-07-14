<?php

declare(strict_types=1);

namespace JSONAPI\OAS;

/**
 * Class SecurityRequirement
 *
 * @package JSONAPI\OAS
 */
class SecurityRequirement implements \JsonSerializable
{
    private array $requirements = [];

    public function addRequirements(string $name, array $requirements)
    {
        $this->requirements[$name] = $requirements;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return (object)$this->requirements;
    }
}
