<?php


namespace JSONAPI\Exception;


use Exception;
use Fig\Http\Message\StatusCodeInterface;

class HttpException extends Exception
{
    protected $message = 'Bad Request';
    protected $status = StatusCodeInterface::STATUS_BAD_REQUEST;

    public function getStatus()
    {
        return $this->status;
    }
}
