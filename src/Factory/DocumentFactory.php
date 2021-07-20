<?php

/**
 * Created by tomas
 * at 06.04.2021 20:44
 */

declare(strict_types=1);

namespace JSONAPI\Factory;

use JSONAPI\Document\Attribute;
use JSONAPI\Document\Deserializable;
use JSONAPI\Document\Document;
use JSONAPI\Document\Id;
use JSONAPI\Document\Relationship;
use JSONAPI\Document\ResourceCollection;
use JSONAPI\Document\ResourceObject;
use JSONAPI\Document\ResourceObjectIdentifier;
use JSONAPI\Document\Type;
use JSONAPI\Exception\Document\AlreadyInUse;
use JSONAPI\Exception\Document\ForbiddenCharacter;
use JSONAPI\Exception\Document\ForbiddenDataType;
use JSONAPI\Exception\Http\Conflict;
use JSONAPI\Exception\Http\UnexpectedFieldDataType;
use JSONAPI\Exception\Metadata\MetadataException;
use JSONAPI\Exception\Metadata\MetadataNotFound;
use JSONAPI\Metadata\ClassMetadata;
use JSONAPI\Metadata\MetadataRepository;
use JSONAPI\URI\Path\PathInterface;
use JsonException;
use ReflectionClass;
use ReflectionException;
use Swaggest\JsonSchema\Exception as SwaggestException;
use Swaggest\JsonSchema\InvalidValue;
use Swaggest\JsonSchema\Schema;
use Swaggest\JsonSchema\SchemaContract;

class DocumentFactory
{
    /**
     * @var PathInterface
     */
    private PathInterface $path;
    /**
     * @var MetadataRepository
     */
    private MetadataRepository $repository;
    /**
     * @var ClassMetadata
     */
    private ClassMetadata $metadata;
    /**
     * @var SchemaContract
     */
    private SchemaContract $input;

    /**
     * DocumentFactory constructor.
     *
     * @param MetadataRepository $repository
     * @param PathInterface      $path
     *
     * @throws SwaggestException
     */
    public function __construct(MetadataRepository $repository, PathInterface $path)
    {
        $this->repository = $repository;
        $this->path       = $path;
        $this->input      = Schema::import(
            json_decode(file_get_contents(__DIR__ . '/../Middleware/in.json'))
        );
    }


    /**
     * @param string $json
     *
     * @return Document
     * @throws AlreadyInUse
     * @throws Conflict
     * @throws ForbiddenCharacter
     * @throws ForbiddenDataType
     * @throws MetadataException
     * @throws MetadataNotFound
     * @throws InvalidValue
     * @throws JsonException
     */
    public function decode(string $json): Document
    {
        $json = json_decode($json, false, 512, JSON_THROW_ON_ERROR);
        $this->input->in($json);
        $document       = new Document();
        $type           = $this->path->getPrimaryResourceType();
        $this->metadata = $this->repository->getByType($type);
        if ($this->path->isCollection()) {
            $data = $this->parseCollection($json->data);
        } else {
            $data = $this->parseResource($json->data);
        }
        $document->setData($data);
        return $document;
    }

    /**
     * @param array $collection
     *
     * @return ResourceCollection
     * @throws AlreadyInUse
     * @throws Conflict
     * @throws ForbiddenCharacter
     * @throws ForbiddenDataType
     */
    private function parseCollection(array $collection): ResourceCollection
    {
        $data = new ResourceCollection();
        if (!empty($collection)) {
            foreach ($collection as $object) {
                $resource = $this->parseResource($object);
                $data->add($resource);
            }
        }
        return $data;
    }

    /**
     * @param object $object
     *
     * @return ResourceObject|ResourceObjectIdentifier
     * @throws AlreadyInUse
     * @throws Conflict
     * @throws ForbiddenCharacter
     * @throws ForbiddenDataType
     * @throws UnexpectedFieldDataType
     */
    private function parseResource(object $object): ResourceObject | ResourceObjectIdentifier
    {
        if ($object->type !== $this->metadata->getType()) {
            throw new Conflict("Provided resource type {$object->type} has different type
            than expected {$this->metadata->getType()}.");
        }
        $type     = new Type($object->type);
        $id       = new Id(@$object->id);
        $resource = new ResourceObjectIdentifier($type, $id);
        if (!$this->path->isRelationship()) {
            $resource = new ResourceObject($type, $id);
            if (property_exists($object, 'attributes')) {
                foreach ($this->parseAttributes($object) as $attribute) {
                    $resource->addAttribute($attribute);
                }
            }
            if (property_exists($object, 'relationships')) {
                foreach ($this->parseRelationships($object) as $relationship) {
                    $resource->addRelationship($relationship);
                }
            }
        }
        return $resource;
    }

    /**
     * @param object $object
     *
     * @return array
     * @throws ForbiddenCharacter
     * @throws UnexpectedFieldDataType
     */
    private function parseAttributes(object $object): array
    {
        $attributes = [];
        foreach ($this->metadata->getAttributes() as $attribute) {
            if (property_exists($object->attributes, $attribute->name)) {
                $value = $object->attributes->{$attribute->name};
                if (!is_null($value)) {
                    switch ($attribute->type) {
                        case 'int':
                            if (!is_int($value)) {
                                throw new UnexpectedFieldDataType($attribute->name, gettype($value), 'int');
                            }
                            break;
                        case 'bool':
                            if (!is_bool($value)) {
                                throw new UnexpectedFieldDataType($attribute->name, gettype($value), 'bool');
                            }
                            break;
                        case 'float':
                            if (!is_int($value) && !is_float($value)) {
                                throw new UnexpectedFieldDataType($attribute->name, gettype($value), 'float');
                            }
                            $value = floatval($value);
                            break;
                        case 'string':
                            if (!is_string($value)) {
                                throw new UnexpectedFieldDataType($attribute->name, gettype($value), 'string');
                            }
                            break;
                        default:
                            break;
                    }
                } elseif ($attribute->nullable === false) {
                    throw new UnexpectedFieldDataType($attribute->name, gettype($value), 'not null');
                }
                try {
                    $className = $attribute->type;
                    if ((new ReflectionClass($className))->implementsInterface(Deserializable::class)) {
                        /** @var Deserializable $className */
                        $value = $className::jsonDeserialize($value);
                    }
                } catch (ReflectionException) {
                    //NOSONAR
                }
                $attributes[] = new Attribute($attribute->name, $value);
            }
        }
        return $attributes;
    }

    /**
     * @param object $object
     *
     * @return array
     * @throws ForbiddenCharacter
     * @throws ForbiddenDataType
     * @throws UnexpectedFieldDataType
     */
    private function parseRelationships(object $object): array
    {
        $relationships = [];
        foreach ($this->metadata->getRelationships() as $relationship) {
            if (property_exists($object->relationships, $relationship->name)) {
                $value = $object->relationships->{$relationship->name}->data;
                if (!is_null($value)) {
                    if ($relationship->isCollection) {
                        $data = new ResourceCollection();
                        foreach ($value as $item) {
                            $data->add(new ResourceObjectIdentifier(new Type($item->type), new Id($item->id)));
                        }
                    } else {
                        $data = new ResourceObjectIdentifier(new Type($value->type), new Id($value->id));
                    }
                } elseif ($relationship->nullable === false) {
                    throw new UnexpectedFieldDataType($relationship->name, gettype($value), 'not null');
                } else {
                    $data = $value;
                }
                $rel = new Relationship($relationship->name);
                $rel->setData($data);
                $relationships[] = $rel;
            }
        }
        return $relationships;
    }
}
