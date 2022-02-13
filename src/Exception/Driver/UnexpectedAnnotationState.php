<?php

/**
 * Created by tomas
 * at 13.02.2022 21:41
 */

declare(strict_types=1);

namespace JSONAPI\Exception\Driver;

class UnexpectedAnnotationState extends DriverException
{
    /**
     * UnexpectedAnnotationState constructor.
     *
     * @param string $reason
     */
    public function __construct(string $reason = '')
    {
        $message = sprintf("Unexpected annotation state. %s", $reason);
        parent::__construct($message, 531);
    }
}
