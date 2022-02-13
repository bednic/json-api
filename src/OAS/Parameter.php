<?php

declare(strict_types=1);

namespace JSONAPI\OAS;

use JSONAPI\Document\Serializable;
use JSONAPI\Exception\OAS\ExclusivityCheckException;
use JSONAPI\Exception\OAS\IncompleteObjectException;
use JSONAPI\Exception\OAS\OpenAPIException;
use JSONAPI\OAS\Type\In;
use JSONAPI\OAS\Type\Style;
use ReflectionClass;

/**
 * Class Parameter
 *
 * @package JSONAPI\OAS
 */
class Parameter extends Reference implements Serializable
{
    /**
     * Case sensitive
     *
     * @var string
     */
    private string $name;
    /**
     * @var In
     */
    private In $in;
    /**
     * @var string|null
     */
    private ?string $description = null;
    /**
     * Default is false
     *
     * @var bool|null
     */
    private ?bool $required = null;
    /**
     * Default is false
     *
     * @var bool|null
     */
    private ?bool $deprecated = null;
    /**
     * Default is false
     *
     * @deprecated v3.0.3
     * @var bool|null
     */
    private ?bool $allowEmptyValue = null;
    /**
     * @var Style
     */
    private Style $style;
    /**
     * Default is false
     *
     * @var bool|null
     */
    private ?bool $explode = null;
    /**
     * Default is false
     *
     * @var bool|null
     */
    private ?bool $allowReserved = null;
    /**
     * @var Schema|null
     */
    private ?Schema $schema = null;
    /**
     * @var mixed
     */
    private $example;
    /**
     * @var Example[]
     */
    private array $examples = [];

    /**
     * @var MediaType[]
     */
    private array $content = [];

    /**
     * Parameter constructor.
     *
     * @param string $name
     * @param In     $in
     */
    public function __construct(string $name, In $in)
    {
        $this->name = $name;
        $this->in   = $in;
        if ($in === In::PATH) {
            $this->required = true;
            $this->style    = Style::SIMPLE;
        }
        if ($in === In::QUERY) {
            $this->style = Style::FORM;
        }
        if ($in === In::HEADER) {
            $this->style = Style::SIMPLE;
        }
        if ($in === In::COOKIE) {
            $this->style = Style::FORM;
        }
    }

    /**
     * @param string                                                                            $to
     * @param SecurityScheme|Schema|Response|RequestBody|Parameter|Header|Link|Example|Callback $origin
     *
     * @return Parameter
     * @throws OpenAPIException
     */
    public static function createReference(string $to, $origin): Parameter
    {
        try {
            /** @var Parameter $static */
            $static = (new ReflectionClass(__CLASS__))->newInstanceWithoutConstructor(); //NOSONAR
            $static->setRef($to, $origin);
            return $static;
        } catch (\ReflectionException $exception) {
            throw OpenAPIException::createFromPrevious($exception);
        }
    }

    /**
     * @return string
     */
    public function getUID(): string
    {
        if ($this->isReference()) {
            return $this->origin->getUID();
        }
        return $this->name . $this->in->value;
    }

    /**
     * @param string $description
     *
     * @return Parameter
     */
    public function setDescription(string $description): Parameter
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @param bool $required
     *
     * @return Parameter
     */
    public function setRequired(bool $required): Parameter
    {
        $this->required = $required;
        return $this;
    }

    /**
     * @param bool $deprecated
     *
     * @return Parameter
     */
    public function setDeprecated(bool $deprecated): Parameter
    {
        $this->deprecated = $deprecated;
        return $this;
    }

    /**
     * @param bool $allowEmptyValue
     *
     * @return Parameter
     * @deprecated
     */
    public function setAllowEmptyValue(bool $allowEmptyValue): Parameter
    {
        $this->allowEmptyValue = $allowEmptyValue;
        return $this;
    }

    /**
     * @param Style $style
     *
     * @return Parameter
     */
    public function setStyle(Style $style): Parameter
    {
        $this->style = $style;
        if ($style === Style::FORM) {
            $this->explode = true;
        }
        return $this;
    }

    /**
     * @param bool $explode
     *
     * @return Parameter
     */
    public function setExplode(bool $explode): Parameter
    {
        $this->explode = $explode;
        return $this;
    }

    /**
     * Determines whether the parameter value SHOULD allow reserved characters, as defined by RFC3986
     * :/?#[]@!$&'()*+,;= to be included without percent-encoding. This property only applies to parameters with an in
     * value of query. The default value is false
     *
     * @param bool $allowReserved
     *
     * @return Parameter
     */
    public function setAllowReserved(bool $allowReserved): Parameter
    {
        $this->allowReserved = $allowReserved;
        return $this;
    }

    /**
     * @param Schema $schema
     *
     * @return Parameter
     */
    public function setSchema(Schema $schema): Parameter
    {
        $this->schema = $schema;
        return $this;
    }

    /**
     * @param mixed $example
     *
     * @return Parameter
     * @throws ExclusivityCheckException
     */
    public function setExample($example)
    {
        if ($this->examples) {
            throw new ExclusivityCheckException();
        }
        $this->example = $example;
        return $this;
    }

    /**
     * @param string  $key
     * @param Example $example
     *
     * @return Parameter
     * @throws ExclusivityCheckException
     */
    public function addExample(string $key, Example $example): Parameter
    {
        if ($this->example) {
            throw new ExclusivityCheckException();
        }
        $this->examples[$key] = $example;
        return $this;
    }

    /**
     * @param string    $key
     * @param MediaType $content
     *
     * @return Parameter
     */
    public function setContent(string $key, MediaType $content): Parameter
    {
        $this->content       = [];
        $this->content[$key] = $content;
        return $this;
    }

    /**
     * @return object
     * @throws IncompleteObjectException
     */
    public function jsonSerialize(): object
    {
        if ($this->isReference()) {
            return parent::jsonSerialize();
        }
        if (empty($this->schema) && empty($this->content)) {
            throw new IncompleteObjectException();
        }
        $ret = [
            'name'  => $this->name,
            'in'    => $this->in,
            'style' => $this->style
        ];
        if (!is_null($this->required)) {
            $ret['required'] = $this->required;
        }
        if (!is_null($this->deprecated)) {
            $ret['deprecated'] = $this->deprecated;
        }
        if (!is_null($this->explode)) {
            $ret['explode'] = $this->explode;
        }
        if (!is_null($this->allowReserved)) {
            $ret['allowReserved'] = $this->allowReserved;
        }
        if ($this->in === In::QUERY && !is_null($this->allowEmptyValue)) {
            $ret['allowEmptyValue'] = $this->allowEmptyValue;
        }
        if ($this->description) {
            $ret['description'] = $this->description;
        }
        if ($this->schema) {
            $ret['schema'] = $this->schema;
        }
        if ($this->example) {
            $ret['example'] = $this->example;
        }
        if ($this->examples) {
            $ret['examples'] = $this->examples;
        }
        if ($this->content) {
            $ret['content'] = $this->content;
        }
        return (object)$ret;
    }

    /**
     * @return string
     */
    protected function getName(): string
    {
        return $this->name;
    }
}
