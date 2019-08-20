<?php


namespace JSONAPI\Test\resources;


use JSONAPI\JsonConvertible;

class DtoValue implements JsonConvertible
{

    /**
     * @var string
     */
    private $stringProperty = 'string-value';

    /**
     * @var int
     */
    private $intProperty = 1234;

    /**
     * @var bool
     */
    private $boolProperty = true;

    /**
     * @return string
     */
    public function getStringProperty(): string
    {
        return $this->stringProperty;
    }

    /**
     * @param string $stringProperty
     */
    public function setStringProperty(string $stringProperty): void
    {
        $this->stringProperty = $stringProperty;
    }

    /**
     * @return int
     */
    public function getIntProperty(): int
    {
        return $this->intProperty;
    }

    /**
     * @param int $intProperty
     */
    public function setIntProperty(int $intProperty): void
    {
        $this->intProperty = $intProperty;
    }

    /**
     * @return bool
     */
    public function isBoolProperty(): bool
    {
        return $this->boolProperty;
    }

    /**
     * @param bool $boolProperty
     */
    public function setBoolProperty(bool $boolProperty): void
    {
        $this->boolProperty = $boolProperty;
    }

    /**
     * @param array $json
     *
     * @return static
     */
    public static function jsonDeserialize($json)
    {
        $json = (array) $json;
        $self = new static();
        $self->setStringProperty($json['stringProperty']);
        $self->setIntProperty($json['intProperty']);
        $self->setBoolProperty($json['boolProperty']);
        return $self;
    }

    /**
     * Specify data which should be serialized to JSON
     *
     * @link  https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return [
            'stringProperty' => $this->getStringProperty(),
            'intProperty' => $this->getIntProperty(),
            'boolProperty' => $this->isBoolProperty()
        ];
    }
}
