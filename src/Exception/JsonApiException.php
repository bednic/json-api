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
    const FACTORY_UNKNOWN = 10;
    const FACTORY_CLASS_IS_NOT_RESOURCE = 11;
    const FACTORY_PATH_IS_NOT_VALID = 12;

    const DRIVER_UNKNOWN = 20;
    const DRIVER_ANNOTATION_NOT_ON_GETTER = 21;

    const DOCUMENT_UNKNOWN = 30;
    const DOCUMENT_HAS_DATA_AND_ERRORS = 31;
    const DOCUMENT_PRIMARY_DATA_TYPE_MISMATCH = 32;
    const DOCUMENT_FORBIDDEN_VALUE_TYPE = 33;
    const DOCUMENT_FORBIDDEN_CHARACTER = 34;

    const ENCODER_UNKNOWN = 40;
    const ENCODER_INVALID_FIELD = 43;
    const ENCODER_CLASS_NOT_EXIST = 44;

    protected static $messages = [
        self::DRIVER_ANNOTATION_NOT_ON_GETTER => [
            DriverException::class,
            "Annotation %s on method MUST be on getter. Method %s on resource %s doesn't seems like getter."
        ],
        self::FACTORY_PATH_IS_NOT_VALID => [
            FactoryException::class,
            "Path to object is not directory."
        ],
        self::FACTORY_CLASS_IS_NOT_RESOURCE => [
            FactoryException::class,
            "Metadata for class %s does not exists."
        ],
        self::DOCUMENT_UNKNOWN => [
            DocumentException::class,
            "Unknown document error occurred."
        ],
        self::DOCUMENT_HAS_DATA_AND_ERRORS => [
            DocumentException::class,
            "Non-valid document. Data AND Errors are set. Only Data XOR Errors are allowed"
        ],
        self::DOCUMENT_PRIMARY_DATA_TYPE_MISMATCH => [
            DocumentException::class,
            "Primary data type mismatch from type gathered from url."
        ],
        self::DRIVER_UNKNOWN => [
            DriverException::class,
            "Unknown driver error occurred."
        ],
        self::FACTORY_UNKNOWN => [
            FactoryException::class,
            "Unknown factory error occurred."
        ],
        self::ENCODER_UNKNOWN => [
            EncoderException::class,
            "Unknown encoder error occurred."
        ],
        self::DOCUMENT_FORBIDDEN_VALUE_TYPE => [
            EncoderException::class,
            "Attribute value type is not supported"
        ],
        self::DOCUMENT_FORBIDDEN_CHARACTER => [
            EncoderException::class,
            "Attribute name character violation."
        ],
        self::ENCODER_INVALID_FIELD => [
            EncoderException::class,
            "Field %s is not Attribute nor Relationship"
        ],
        self::ENCODER_CLASS_NOT_EXIST => [
            EncoderException::class,
            "Class %s does not exist."
        ]

    ];

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return 500;
    }

    /**
     * @param int  $code
     * @param null $args
     * @return JsonApiException|DriverException|FactoryException|EncoderException|DocumentException
     */
    protected static function for(int $code,array $args = [])
    {
        return new self::$messages[$code][0](printf(self::$messages[$code][1], ...$args), $code);
    }

}
