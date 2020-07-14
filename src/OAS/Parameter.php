<?php

declare(strict_types=1);

namespace JSONAPI\OAS;

use JSONAPI\OAS\Enum\In;
use JSONAPI\OAS\Enum\Style;
use JSONAPI\OAS\Exception\ExclusivityCheckException;
use JSONAPI\OAS\Exception\IncompleteObjectException;

/**
 * Class Parameter
 *
 * @package JSONAPI\OAS
 */
class Parameter extends Reference implements \JsonSerializable
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
     * @var array<string, Example>
     */
    private array $examples = [];

    /**
     * @var array<string, MediaType>
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
        if ($in->equals(In::PATH())) {
            $this->required = true;
            $this->style    = Style::SIMPLE();
        }
        if ($in->equals(In::QUERY())) {
            $this->style = Style::FORM();
        }
        if ($in->equals(In::HEADER())) {
            $this->style = Style::SIMPLE();
        }
        if ($in->equals(In::COOKIE())) {
            $this->style = Style::FORM();
        }
    }

    /**
     * @return string
     */
    protected function getName(): string
    {
        return $this->name;
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
        if ($style->equals(Style::FORM())) {
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
    public function jsonSerialize()
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
        if ($this->in->equals(In::QUERY()) && !is_null($this->allowEmptyValue)) {
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
     * @param string $to
     *
     * @return Parameter
     */
    public static function createReference(string $to): Parameter
    {
        /** @var Parameter $static */
        $static = (new \ReflectionClass(__CLASS__))->newInstanceWithoutConstructor();
        $static->setRef($to);
        return $static;
    }
}
