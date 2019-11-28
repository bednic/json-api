<?php


namespace JSONAPI\Exception\Document;


use JSONAPI\Exception\JsonApiException;

class DocumentException extends JsonApiException
{
    protected $code = 20;
    protected $message = "Unknown Document Exception.";
}
