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
    /**
     * Each name MUST correspond to a security scheme which is declared in the Security Schemes under the Components
     * Object. If the security scheme is of type "oauth2" or "openIdConnect", then the value is a list of scope names
     * required for the execution, and the list MAY be empty if authorization does not require a specified scope. For
     * other security scheme types, the array MUST be empty.
     *
     * @var string[]
     */
    private array $requirements = [];

    /**
     * @param string $name of SecurityScheme in Components
     * @param array  $requirements
     */
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
