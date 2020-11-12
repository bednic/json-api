<?php

declare(strict_types=1);

namespace JSONAPI\Document\Error;

use JSONAPI\Document\Error;
use Throwable;

/**
 * Interface Factory
 *
 * @package JSONAPI\Document\Error
 */
interface ErrorFactory
{
    /**
     * Method transform Throwable to Error
     *
     * @param Throwable $exception
     *
     * @return mixed
     */
    public function fromThrowable(Throwable $exception): Error;
}
