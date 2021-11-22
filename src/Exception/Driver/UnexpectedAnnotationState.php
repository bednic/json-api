<?php

/**
 * Created by lasicka@logio.cz
 * at 22.11.2021 12:04
 */

declare(strict_types=1);

namespace JSONAPI\Exception\Driver;

/**
 * Class UnexpectedAnnotationState
 *
 * @package JSONAPI\Exception\Driver
 */
class UnexpectedAnnotationState extends DriverException
{
    protected $code = 531;
    protected $message = "Unexpected annotation state. %s";

    /**
     * UnexpectedAnnotationState constructor.
     *
     */
    public function __construct(string $reason = '')
    {
        $message = sprintf($this->message, $reason);
        parent::__construct($message);
    }
}
