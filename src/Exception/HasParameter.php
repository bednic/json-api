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
    /**
     * @return string
     */
    public function getParameter(): string;
}
