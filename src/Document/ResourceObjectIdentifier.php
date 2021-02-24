<?php

declare(strict_types=1);

namespace JSONAPI\Document;

use JSONAPI\Data\Collection;
use JSONAPI\Exception\Document\AlreadyInUse;
use stdClass;

/**
 * Class ResourceObjectIdentifier
 *
 * @package JSONAPI\Document
 */
class ResourceObjectIdentifier implements Serializable, HasMeta, PrimaryData
{
    use MetaExtension;

    /**
     * @var Collection<Field>
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
        $this->fields = new Collection();
        try {
            $this->addField($type);
            $this->addField($id);
        } catch (AlreadyInUse $ignored) {
            // NO-SONAR
        }
    }

    /**
     * @param Field $field
     *
     * @throws AlreadyInUse
     */
    protected function addField(Field $field): void
    {
        if ($this->fields->hasKey($field->getKey())) {
            throw new AlreadyInUse($field->getKey());
        }
        $this->fields->set($field->getKey(), $field);
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
     * Specify data which should be serialized to JSON
     *
     * @link  https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return object data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize(): object
    {
        $ret = new stdClass();
        $ret->type = $this->fields->get('type');
        $ret->id = $this->fields->get('id');
        if ($this->hasMeta()) {
            $ret->meta = $this->getMeta();
        }
        return $ret;
    }
}
