<?php

declare(strict_types=1);

namespace JSONAPI\Exception;

/**
 * Interface HasPointer
 *
 * @package JSONAPI\Exception
 */
interface HasPointer
{
    public function getPointer(): string;
}
