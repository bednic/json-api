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
    const DOCUMENT_UNKNOWN = 30;
    const DOCUMENT_HAS_DATA_AND_ERRORS = 31;
    const DOCUMENT_PRIMARY_DATA_TYPE_MISMATCH = 32;
    const DOCUMENT_FORBIDDEN_VALUE_TYPE = 33;
    const DOCUMENT_FORBIDDEN_CHARACTER = 34;
}
