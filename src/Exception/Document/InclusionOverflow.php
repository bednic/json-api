<?php

namespace JSONAPI\Exception\Document;

class InclusionOverflow extends DocumentException
{
    protected $code = 524;
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
