<?php

/**
 * Created by tomas
 * at 20.03.2021 16:56
 */

declare(strict_types=1);

namespace JSONAPI\Encoding;

use DateTimeInterface;
use JSONAPI\Data\Collection;
use JSONAPI\Document\ResourceCollection;
use JSONAPI\Document\ResourceObject;
use JSONAPI\Document\ResourceObjectIdentifier;
use JSONAPI\Exception\Metadata\InvalidField;
use JSONAPI\Factory\LinkComposer;
use JSONAPI\Metadata\Attribute;
use JSONAPI\Metadata\ClassMetadata;
use JSONAPI\Metadata\Field;
use JSONAPI\Metadata\MetadataRepository;
use JSONAPI\Metadata\Relationship;
use JSONAPI\URI\Fieldset\FieldsetInterface;
use Psr\Log\LoggerInterface;

abstract class FieldsProcessor
{

    private ?FieldsetInterface $fieldset;

    /**
     * FieldsProcessor constructor.
     *
     * @param FieldsetInterface|null $fieldset
     */
    public function __construct(?FieldsetInterface $fieldset)
    {
        $this->fieldset = $fieldset;
    }


    /**
     * @param Field  $field
     * @param object $object
     *
     * @return mixed
     */
    protected function getValue(Field $field, object $object): mixed
    {
        $value = null;
        if ($field->getter != null) {
            $value = call_user_func([$object, $field->getter]);
        } else {
            $value = $object->{$field->property};
        }
        return $value;
    }
    /**
     * @param Field          $field
     * @param ResourceObject $resource
     *
     * @return bool
     */
    protected function showField(Field $field, ResourceObject $resource): bool
    {
        return $this->fieldset?->showField($resource->getType(), $field->name) ?? true;
    }
}
