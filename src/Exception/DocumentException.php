<?php
/**
 * Created by IntelliJ IDEA.
 * User: tomas
 * Date: 04.02.2019
 * Time: 14:53
 */

namespace JSONAPI\Exception;

/**
 * Class DocumentException
 *
 * @package JSONAPI\Exception
 */
class DocumentException extends JsonApiException
{
    const UNKNOWN = 30;
    const HAS_DATA_AND_ERRORS = 31;
    const PRIMARY_DATA_TYPE_MISMATCH = 32;
    const FORBIDDEN_VALUE_TYPE = 33;
    const FORBIDDEN_CHARACTER = 34;
}
