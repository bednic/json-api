<?php

declare(strict_types=1);

namespace JSONAPI\Exception;

/**
 * Interface HasParameter
 *
 * @package JSONAPI\Exception
 */
interface HasParameter
{
    public function getParameter(): string;
}
