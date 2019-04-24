<?php
/**
 * Created by IntelliJ IDEA.
 * User: tomas
 * Date: 16.04.2019
 * Time: 13:49
 */

namespace JSONAPI\Exception;


abstract class JsonApiException extends \Exception
{
    const CODE_CLASS_IS_NOT_RESOURCE = 11;
    const CODE_PATH_IS_NOT_VALID = 12;

}
