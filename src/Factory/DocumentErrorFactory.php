<?php

declare(strict_types=1);

namespace JSONAPI\Factory;

use Fig\Http\Message\StatusCodeInterface;
use JSONAPI\Document\Error;
use JSONAPI\Document\Error\ErrorFactory;
use JSONAPI\Document\Error\Source;
use JSONAPI\Exception\HasParameter;
use JSONAPI\Exception\HasPointer;
use JSONAPI\Exception\JsonApiException;
use Swaggest\JsonSchema\Exception\Error as SchemaError;
use Swaggest\JsonSchema\InvalidValue as SchemaInvalidValue;
use Throwable;

/**
 * Class DocumentErrorFactory
 *
 * @package JSONAPI\Factory
 */
class DocumentErrorFactory implements ErrorFactory
{

    /**
     * @inheritDoc
     */
    public function fromThrowable(Throwable $exception): Error
    {
        $error = new Error();
        $error->setStatus(StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR);
        $error->setCode((string)$exception->getCode());
        $error->setDetail($exception->getMessage());
        if ($exception instanceof JsonApiException) {
            $this->processJsonApiException($exception, $error);
        } elseif ($exception instanceof SchemaInvalidValue) {
            $this->processSchemaInvalidValue($exception, $error);
        }
        return $error;
    }

    private function processJsonApiException(JsonApiException $exception, Error $error): void
    {
        $error->setTitle($exception->getTitle());
        $error->setStatus($exception->getStatus());
        if ($exception instanceof HasPointer) {
            $error->setSource(Source::pointer($exception->getPointer()));
        } elseif ($exception instanceof HasParameter) {
            $error->setSource(Source::parameter($exception->getParameter()));
        }
    }

    private function processSchemaInvalidValue(SchemaInvalidValue $exception, Error $error): void
    {
        list($message, $source) = self::parseInvalidValue($exception->inspect());
        $error->setDetail($message);
        $error->setSource($source);
    }

    /**
     * @param SchemaError $error
     *
     * @return array<string|Source>
     * @example [
     *      <string> message,
     *      <ErrorSource> source
     * ]
     */
    private static function parseInvalidValue(SchemaError $error): array
    {
        if ($error->subErrors) {
            return self::parseInvalidValue($error->subErrors[0]);
        } else {
            return [
                (string)preg_replace('/, data.+/', '', $error->error ?? ''),
                Source::pointer($error->dataPointer)
            ];
        }
    }
}
