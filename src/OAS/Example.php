<?php

declare(strict_types=1);

namespace JSONAPI\OAS;

use JSONAPI\Document\Serializable;
use JSONAPI\Exception\OAS\ExclusivityCheckException;
use JSONAPI\Exception\OAS\OpenAPIException;
use ReflectionClass;
use ReflectionException;

/**
 * Class Example
 *
 * @package JSONAPI\OAS
 */
class Example extends Reference implements Serializable
{
    /**
     * @var string|null
     */
    private ?string $summary = null;
    /**
     * @var string|null
     */
    private ?string $description = null;
    /**
     * The value field and externalValue field are mutually exclusive
     *
     * @var mixed
     */
    private $value;
    /**
     * @var string|null
     */
    private ?string $externalValue = null;

    /**
     * @param string                                                                            $to
     * @param SecurityScheme|Schema|Response|RequestBody|Parameter|Header|Link|Example|Callback $origin
     *
     * @return Example
     * @throws OpenAPIException
     */
    public static function createReference(string $to, $origin): Example
    {
        try {
            /** @var Example $static */
            $static = (new ReflectionClass(__CLASS__))->newInstanceWithoutConstructor(); //NOSOAR
            $static->setRef($to, $origin);
            return $static;
        } catch (ReflectionException $exception) {
            throw OpenAPIException::createFromPrevious($exception);
        }
    }

    /**
     * @param string|null $summary
     *
     * @return Example
     */
    public function setSummary(?string $summary): Example
    {
        $this->summary = $summary;
        return $this;
    }

    /**
     * @param string|null $description
     *
     * @return Example
     */
    public function setDescription(?string $description): Example
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @param mixed $value
     *
     * @return Example
     * @throws ExclusivityCheckException
     */
    public function setValue($value)
    {
        if ($this->externalValue) {
            throw new ExclusivityCheckException();
        }
        $this->value = $value;
        return $this;
    }

    /**
     * @param string|null $externalValue
     *
     * @return Example
     * @throws ExclusivityCheckException
     */
    public function setExternalValue(?string $externalValue): Example
    {
        if ($this->value) {
            throw new ExclusivityCheckException();
        }
        $this->externalValue = $externalValue;
        return $this;
    }

    /**
     * @return object
     */
    public function jsonSerialize(): object
    {
        if ($this->isReference()) {
            return parent::jsonSerialize();
        }
        $ret = [];
        if ($this->summary) {
            $ret['summary'] = $this->summary;
        }
        if ($this->description) {
            $ret['description'] = $this->description;
        }
        if ($this->value) {
            $ret['value'] = $this->value;
        }
        if ($this->externalValue) {
            $ret['externalValue'] = $this->externalValue;
        }
        return (object)$ret;
    }
}
