<?php

declare(strict_types=1);

namespace JSONAPI\OAS;

use JSONAPI\Document\Serializable;
use JSONAPI\Exception\OAS\IncompleteObjectException;
use JSONAPI\Exception\OAS\InvalidArgumentException;
use JSONAPI\Exception\OAS\OpenAPIException;
use ReflectionClass;

/**
 * Class Schema
 *
 * @package JSONAPI\OAS
 */
class Schema extends Reference implements Serializable
{
    /**
     * @var string|null
     */
    private ?string $title = null;
    /**
     * Greater then 0
     *
     * @var int|null
     */
    private ?int $multipleOf = null;
    /**
     * @var int|null
     */
    private ?int $maximum = null;
    /**
     * @var bool|null
     */
    private ?bool $exclusiveMaximum = null;
    /**
     * @var int|null
     */
    private ?int $minimum = null;
    /**
     * @var bool
     */
    private ?bool $exclusiveMinimum = null;
    /**
     * Greater or equal to 0
     *
     * @var int|null
     */
    private ?int $maxLength = null;
    /**
     * @var int|null
     */
    private ?int $minLength = null;
    /**
     * @var string|null
     */
    private ?string $pattern = null;
    /**
     * @var int|null
     */
    private ?int $maxItems = null;
    /**
     * @var int|null
     */
    private ?int $minItems = null;
    /***
     * @var bool
     */
    private ?bool $uniqueItems = null;
    /**
     * @var int|null
     */
    private ?int $maxProperties = null;
    /**
     * @var int|null
     */
    private ?int $minProperties = null;
    /**
     * @var string[]
     */
    private ?array $required = null;
    /**
     * @var array<string>
     */
    private array $enum = [];
    /**
     * @var string|null
     */
    private ?string $type = null;
    /**
     * @var Schema[]
     */
    private array $allOf = [];
    /**
     * @var Schema[]
     */
    private array $oneOf = [];

    /**
     * @var Schema[]
     */
    private array $anyOf = [];
    /**
     * @var Schema|null
     */
    private ?Schema $not = null;
    /**
     * Must by present if ::type is 'array'
     *
     * @var Schema|null
     */
    private ?Schema $items = null;
    /**
     * @var Schema[]
     */
    private array $properties = [];
    /**
     * @var bool|Schema|null
     */
    private Schema|bool|null $additionalProperties = null;
    /**
     * @var string|null
     */
    private ?string $description = null;
    /**
     * @var string|null
     */
    private ?string $format = null;
    /**
     * @var mixed
     */
    private mixed $default = null;
    /**
     * @var bool|null
     */
    private ?bool $nullable = null;
    /**
     * @var Discriminator|null
     */
    private ?Discriminator $discriminator = null;
    /**
     * @var bool|null
     */
    private ?bool $readOnly = null;
    /**
     * @var bool|null
     */
    private ?bool $writeOnly = null;
    /**
     * @var XML|null
     */
    private ?XML $xml = null;
    /**
     * @var ExternalDocumentation|null
     */
    private ?ExternalDocumentation $externalDocs = null;
    /**
     * @var mixed
     */
    private mixed $example = null;
    /**
     * @var bool
     */
    private ?bool $deprecated = null;

    /**
     * @inheritDoc
     */
    public static function createReference(string $to, $origin): Schema
    {
        try {
            /** @var Schema $static */
            $static = (new ReflectionClass(__CLASS__))->newInstanceWithoutConstructor(); //NOSONAR
            $static->setRef($to, $origin);
            return $static;
        } catch (\ReflectionException $exception) {
            throw OpenAPIException::createFromPrevious($exception);
        }
    }

    public static function new(): Schema
    {
        return new Schema();
    }

    /**
     * @param string $title
     *
     * @return Schema
     */
    public function setTitle(string $title): Schema
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @param int $multipleOf
     *
     * @return Schema
     */
    public function setMultipleOf(int $multipleOf): Schema
    {
        $this->multipleOf = $multipleOf;
        return $this;
    }

    /**
     * @param int $maximum
     *
     * @return Schema
     */
    public function setMaximum(int $maximum): Schema
    {
        $this->maximum = $maximum;
        return $this;
    }

    /**
     * @param bool $exclusiveMaximum
     *
     * @return Schema
     */
    public function setExclusiveMaximum(bool $exclusiveMaximum): Schema
    {
        $this->exclusiveMaximum = $exclusiveMaximum;
        return $this;
    }

    /**
     * @param int $minimum
     *
     * @return Schema
     */
    public function setMinimum(int $minimum): Schema
    {
        $this->minimum = $minimum;
        return $this;
    }

    /**
     * @param bool $exclusiveMinimum
     *
     * @return Schema
     */
    public function setExclusiveMinimum(bool $exclusiveMinimum): Schema
    {
        $this->exclusiveMinimum = $exclusiveMinimum;
        return $this;
    }

    /**
     * @param int $maxLength
     *
     * @return Schema
     */
    public function setMaxLength(int $maxLength): Schema
    {
        $this->maxLength = $maxLength;
        return $this;
    }

    /**
     * @param int $minLength
     *
     * @return Schema
     */
    public function setMinLength(int $minLength): Schema
    {
        $this->minLength = $minLength;
        return $this;
    }

    /**
     * @param string $pattern
     *
     * @return Schema
     */
    public function setPattern(string $pattern): Schema
    {
        $this->pattern = $pattern;
        return $this;
    }

    /**
     * @param int $maxItems
     *
     * @return Schema
     */
    public function setMaxItems(int $maxItems): Schema
    {
        $this->maxItems = $maxItems;
        return $this;
    }

    /**
     * @param int $minItems
     *
     * @return Schema
     */
    public function setMinItems(int $minItems): Schema
    {
        $this->minItems = $minItems;
        return $this;
    }

    /**
     * @param bool $uniqueItems
     *
     * @return Schema
     */
    public function setUniqueItems(bool $uniqueItems): Schema
    {
        $this->uniqueItems = $uniqueItems;
        return $this;
    }

    /**
     * @param int $maxProperties
     *
     * @return Schema
     */
    public function setMaxProperties(int $maxProperties): Schema
    {
        $this->maxProperties = $maxProperties;
        return $this;
    }

    /**
     * @param int $minProperties
     *
     * @return Schema
     */
    public function setMinProperties(int $minProperties): Schema
    {
        $this->minProperties = $minProperties;
        return $this;
    }

    /**
     * @param string[] $required
     *
     * @return Schema
     */
    public function setRequired(array $required): Schema
    {
        $this->required = $required;
        return $this;
    }

    /**
     * @param mixed[] $enum
     *
     * @return Schema
     */
    public function setEnum(array $enum): Schema
    {
        $this->enum = $enum;
        return $this;
    }

    /**
     * @param string $type
     *
     * @return Schema
     */
    public function setType(string $type): Schema
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @param Schema[] $allOf
     *
     * @return Schema
     */
    public function setAllOf(array $allOf): Schema
    {
        $this->allOf = $allOf;
        return $this;
    }

    /**
     * @param Schema[] $oneOf
     *
     * @return Schema
     */
    public function setOneOf(array $oneOf): Schema
    {
        $this->oneOf = $oneOf;
        return $this;
    }

    /**
     * @param Schema[] $anyOf
     *
     * @return Schema
     */
    public function setAnyOf(array $anyOf): Schema
    {
        $this->anyOf = $anyOf;
        return $this;
    }

    /**
     * @param Schema $not
     *
     * @return Schema
     */
    public function setNot(Schema $not): Schema
    {
        $this->not = $not;
        return $this;
    }

    /**
     * @param Schema $items
     *
     * @return Schema
     */
    public function setItems(Schema $items): Schema
    {
        $this->items = $items;
        return $this;
    }

    /**
     * @param string $name
     * @param Schema $property
     *
     * @return Schema
     */
    public function addProperty(string $name, Schema $property): Schema
    {
        $this->properties[$name] = $property;
        return $this;
    }

    /**
     * @param bool|Schema $additionalProperties
     *
     * @return Schema
     */
    public function setAdditionalProperties(Schema|bool $additionalProperties = true): Schema
    {
        $this->additionalProperties = $additionalProperties;
        return $this;
    }

    /**
     * @param string $description
     *
     * @return Schema
     */
    public function setDescription(string $description): Schema
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @param string $format
     *
     * @return Schema
     */
    public function setFormat(string $format): Schema
    {
        $this->format = $format;
        return $this;
    }

    /**
     * @param mixed $default
     *
     * @return Schema
     */
    public function setDefault($default)
    {
        $this->default = $default;
        return $this;
    }

    /**
     * @param bool $nullable
     *
     * @return Schema
     */
    public function setNullable(bool $nullable): Schema
    {
        $this->nullable = $nullable;
        return $this;
    }

    /**
     * @param Discriminator $discriminator
     *
     * @return Schema
     */
    public function setDiscriminator(Discriminator $discriminator): Schema
    {
        $this->discriminator = $discriminator;
        return $this;
    }

    /**
     * @param bool $readOnly
     *
     * @return Schema
     */
    public function setReadOnly(bool $readOnly): Schema
    {
        $this->readOnly = $readOnly;
        return $this;
    }

    /**
     * @param bool $writeOnly
     *
     * @return Schema
     */
    public function setWriteOnly(bool $writeOnly): Schema
    {
        $this->writeOnly = $writeOnly;
        return $this;
    }

    /**
     * @param XML $xml
     *
     * @return Schema
     */
    public function setXml(XML $xml): Schema
    {
        $this->xml = $xml;
        return $this;
    }

    /**
     * @param ExternalDocumentation $externalDocs
     *
     * @return Schema
     */
    public function setExternalDocs(ExternalDocumentation $externalDocs): Schema
    {
        $this->externalDocs = $externalDocs;
        return $this;
    }

    /**
     * @param mixed $example
     *
     * @return Schema
     */
    public function setExample($example)
    {
        $this->example = $example;
        return $this;
    }

    /**
     * @param bool $deprecated
     *
     * @return Schema
     */
    public function setDeprecated(bool $deprecated): Schema
    {
        $this->deprecated = $deprecated;
        return $this;
    }

    /**
     * @throws IncompleteObjectException
     */
    public function jsonSerialize(): object
    {
        if ($this->isReference()) {
            return parent::jsonSerialize();
        }
        if ($this->type == 'array' && is_null($this->items)) {
            throw new IncompleteObjectException('When type is "array", then ::items are required');
        }
        $ret = [];
        if ($this->title) {
            $ret['title'] = $this->title;
        }
        if ($this->multipleOf) {
            $ret['multipleOf'] = $this->multipleOf;
        }
        if ($this->maximum) {
            $ret['maximum'] = $this->maximum;
        }
        if (!is_null($this->exclusiveMaximum)) {
            $ret['exclusiveMaximum'] = $this->exclusiveMaximum;
        }
        if ($this->minimum) {
            $ret['minimum'] = $this->minimum;
        }
        if (!is_null($this->exclusiveMinimum)) {
            $ret['exclusiveMinimum'] = $this->exclusiveMinimum;
        }
        if ($this->maxLength) {
            $ret['maxLength'] = $this->maxLength;
        }
        if ($this->minLength) {
            $ret['minLength'] = $this->minLength;
        }
        if ($this->pattern) {
            $ret['pattern'] = $this->pattern;
        }
        if ($this->maxItems) {
            $ret['maxItems'] = $this->maxItems;
        }
        if ($this->minItems) {
            $ret['minItems'] = $this->minItems;
        }
        if (!is_null($this->uniqueItems)) {
            $ret['uniqueItems'] = $this->uniqueItems;
        }
        if ($this->maxProperties) {
            $ret['maxProperties'] = $this->maxProperties;
        }
        if ($this->minProperties) {
            $ret['minProperties'] = $this->minProperties;
        }
        if ($this->required) {
            $ret['required'] = $this->required;
        }
        if ($this->enum) {
            $ret['enum'] = $this->enum;
        }
        if ($this->type) {
            $ret['type'] = $this->type;
        }
        if ($this->allOf) {
            $ret['allOf'] = $this->allOf;
        }
        if ($this->oneOf) {
            $ret['oneOf'] = $this->oneOf;
        }
        if ($this->anyOf) {
            $ret['anyOf'] = $this->anyOf;
        }
        if ($this->not) {
            $ret['not'] = $this->not;
        }
        if ($this->items) {
            $ret['items'] = $this->items;
        }
        if ($this->properties) {
            $ret['properties'] = $this->properties;
        }
        if (!is_null($this->additionalProperties)) {
            $ret['additionalProperties'] = $this->additionalProperties;
        }
        if ($this->description) {
            $ret['description'] = $this->description;
        }
        if ($this->format) {
            $ret['format'] = $this->format;
        }
        if ($this->default) {
            $ret['default'] = $this->default;
        }
        if (!is_null($this->nullable)) {
            $ret['nullable'] = $this->nullable;
        }
        if ($this->discriminator) {
            $ret['discriminator'] = $this->discriminator;
        }
        if (!is_null($this->readOnly)) {
            $ret['readOnly'] = $this->readOnly;
        }
        if (!is_null($this->writeOnly)) {
            $ret['writeOnly'] = $this->writeOnly;
        }
        if ($this->xml) {
            $ret['xml'] = $this->xml;
        }
        if ($this->externalDocs) {
            $ret['externalDocs'] = $this->externalDocs;
        }
        if ($this->example) {
            $ret['example'] = $this->example;
        }
        if (!is_null($this->deprecated)) {
            $ret['deprecated'] = $this->deprecated;
        }
        return (object)$ret;
    }
}
