<?php
/**
 * Created by IntelliJ IDEA.
 * User: tomas
 * Date: 24.02.2019
 * Time: 19:47
 */

namespace JSONAPI\Exception\Document;

use Fig\Http\Message\StatusCodeInterface;

/**
 * Class UnsupportedMediaTypeException
 *
 * @package JSONAPI\Exception
 */
class UnsupportedMediaType extends BadRequest
{

    protected $code = StatusCodeInterface::STATUS_UNSUPPORTED_MEDIA_TYPE;
    protected $message = "Unsupported Media Type";
}
