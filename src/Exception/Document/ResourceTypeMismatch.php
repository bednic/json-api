<?php

declare(strict_types=1);

namespace JSONAPI\Exception\Document;

/**
 * Class ResourceTypeMismatch
 *
 * @package JSONAPI\Exception\Document
 */
class ResourceTypeMismatch extends DocumentException
{
    /**
     * @var int
     */
    protected $code = 523;
    /**
     * @var string
     */
    protected $message = "Provided data resource type %s is not same as requested resource type %s.";

    /**
     * ResourceTypeMismatch constructor.
     *
     * @param string $source
     * @param string $target
     */
    public function __construct(string $source, string $target)
    {
        $message = sprintf($this->message, $source, $target);
        parent::__construct($message);
    }
}
