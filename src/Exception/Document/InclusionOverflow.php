<?php

declare(strict_types=1);

namespace JSONAPI\Exception\Document;

/**
 * Class InclusionOverflow
 *
 * @package JSONAPI\Exception\Document
 */
class InclusionOverflow extends DocumentException
{
    protected $code = 525;
    protected $message = "You reached max included items count [%d]. You can increase limit, or decrease items count.";

    /**
     * ForbiddenDataType constructor.
     *
     * @param int $count
     */
    public function __construct(int $count)
    {
        $message = sprintf($this->message, $count);
        parent::__construct($message);
    }
}
