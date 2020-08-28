<?php

declare(strict_types=1);

namespace JSONAPI\Exception\Document;

use JSONAPI\Exception\HasPointer;

/**
 * Class ReservedWord
 *
 * @package JSONAPI\Exception\Document
 */
class AlreadyInUse extends DocumentException implements HasPointer
{
    protected $code = 524;
    protected $message = "Field name %s is reserved or used yet. Please use different field name.";
    private string $field;

    /**
     * ForbiddenDataType constructor.
     *
     * @param string $name
     */
    public function __construct(string $name)
    {
        $message = sprintf($this->message, $name);
        parent::__construct($message);
        $this->field = $name;
    }

    public function getPointer(): string
    {
        return '/data/attributes/' . $this->field;
    }
}
