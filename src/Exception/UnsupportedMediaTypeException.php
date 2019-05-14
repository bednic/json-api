<?php
/**
 * Created by IntelliJ IDEA.
 * User: tomas
 * Date: 24.02.2019
 * Time: 19:47
 */

namespace JSONAPI\Exception;

use Fig\Http\Message\StatusCodeInterface;
use Throwable;

/**
 * Class UnsupportedMediaTypeException
 *
 * @package JSONAPI\Exception
 */
class UnsupportedMediaTypeException extends HttpException
{

    protected $message = "Unsupported Media Type";
    protected $status = StatusCodeInterface::STATUS_UNSUPPORTED_MEDIA_TYPE;
}
