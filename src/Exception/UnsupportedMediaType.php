<?php
/**
 * Created by IntelliJ IDEA.
 * User: tomas
 * Date: 24.02.2019
 * Time: 19:47
 */

namespace JSONAPI\Exception;


class UnsupportedMediaType extends JsonApiException
{
    protected $code = 415;
    protected $message = "Unsupported Media Type";
}
