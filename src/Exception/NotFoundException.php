<?php


namespace JSONAPI\Exception;


use Fig\Http\Message\StatusCodeInterface;

class NotFoundException extends HttpException
{
    protected $message = 'Not Found';
    protected $status = StatusCodeInterface::STATUS_NOT_FOUND;
}
