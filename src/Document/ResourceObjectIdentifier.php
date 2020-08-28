<?php

declare(strict_types=1);

namespace JSONAPI\Document;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use JSONAPI\Exception\Document\AlreadyInUse;
use JSONAPI\Helper\MetaTrait;
use Tools\JSON\JsonSerializable;

/**
 * Class ResourceObjectIdentifier
 *
 * @package JSONAPI\Document
 */
class ResourceObjectIdentifier implements JsonSerializable, HasMeta, PrimaryData
{
    use MetaTrait;

    /**
     * @var Collection|Field[]
     */
    protected Collection $fields;

    /**
     * ResourceObjectIdentifier constructor.
     *
     * @param Type $type
     * @param Id   $id
     */
    public function __construct(Type $type, Id $id)
    {
        $this->fields = new ArrayCollection();
        try {
            $this->addField($type);
            $this->addField($id);
        } catch (AlreadyInUse $ignored) {
            // NO-SONAR
        }
    }

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        if ($this->fields->get('id') !== null) {
            return $this->fields->get('id')->getData();
        }
        return null;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->fields->get('type')->getData();
    }

    /**
     * @param Field $field
     *
     * @throws AlreadyInUse
     */
    protected function addField(Field $field): void
    {
        if ($this->fields->containsKey($field->getKey())) {
            throw new AlreadyInUse($field->getKey());
        }
        $this->fields->set($field->getKey(), $field);
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
        $ret = [
            'type' => $this->fields->get('type'),
            'id' => $this->fields->get('id')
        ];
        if (!$this->getMeta()->isEmpty()) {
            $ret['meta'] = $this->getMeta();
        }
        return $ret;
    }
}
