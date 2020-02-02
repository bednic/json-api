# JSON API

This project goal is create easy-to-use library to implement JSON API specification.
It's accomplished by annotations.

Whole project is at the beginning of development. So don't hesitate to help.
I'm open to some good ideas how make this more customizable and friendly.

This library is tested in production with Doctrine2 and SlimPHP

This library only provides data and wrappers to create valid JSON API document. Controllers and Response is on you.

## Usage

> First we create some object and use annotations to define attributes and relationships

```php
<?php

// Don't forget use Annotations, otherwise you get exception
use \JSONAPI\Annotation as API;

/*
 * Define some objects
 */

/**
 * Class Common
 */
abstract class Common
{

    /**
     * @var string
     */
    protected $id;

    /**
     * IdMetadata
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }
}

/**
 * Class ObjectExample
 *
 * ResourceMetadata("resource")
 */
class ObjectExample extends Common
{
    protected $id = 'uuid';
    /**
     * AttributeMetadata
     * @var string
     */
    public $publicProperty = 'public-value';

    /**
     * @var string
     */
    private $privateProperty = 'private-value';

    /**
     * @var string
     */
    private $readOnlyProperty = 'read-only-value';

    /**
     * @var RelationExample[]
     */
    private $relations = [];

    /**
     * AttributeMetadata
     * @return string
     */
    public function getPrivateProperty(): string
    {
        return $this->privateProperty;
    }

    /**
     * @param string $privateProperty
     */
    public function setPrivateProperty(string $privateProperty): void
    {
        $this->privateProperty = $privateProperty;
    }

    /**
     * AttributeMetadata(setter=false)
     * @return string
     */
    public function getReadOnlyProperty(): string
    {
        return $this->readOnlyProperty;
    }

    /**
     * @API\Relationship(target=RelationExample::class)
     * @return RelationExample[]
     */
    public function getRelations(): array
    {
        return $this->relations;
    }

    /**
     * @param RelationExample[] $relations
     */
    public function setRelations(array $relations): void
    {
        foreach ($relations as $relation) {
            $relation->setObject($this);
        }
        $this->relations = $relations;
    }
}

/**
 * Class RelationExample
 *
 * ResourceMetadata("resource-relation")
 */
class RelationExample extends Common
{
    protected $id = 'relation-uuid';
    /**
     * @var ObjectExample
     */
    private $object;

    /**
     * @API\Relationship(target=ObjectExample::class)
     * @return ObjectExample
     */
    public function getObject(): ObjectExample
    {
        return $this->object;
    }

    /**
     * @param ObjectExample $object
     */
    public function setObject(ObjectExample $object): void
    {
        $this->object = $object;
    }
}
```

> Then just return document from your controller

```php
<?php

class Controller {

    public function getResource(\Psr\Http\Message\ServerRequestInterface $request, \Psr\Http\Message\ResponseInterface $response): \Psr\Http\Message\ResponseInterface
    {
        // Make Document instance
        /** @var \Psr\Http\Message\ServerRequestInterface $request */
        /** @var \JSONAPI\Metadata\MetadataFactory $factory */
        $document = new \JSONAPI\Document\Document($factory, $request);
        
        // Your object which you want to serialize
        $object = new ObjectExample();
        $meta = new \JSONAPI\Document\Meta([
            'count' => 1
        ]);
        // Set your data
        $document->setResource($object);
        $document->setMeta($meta);
        $response->getBody()->write(json_encode($document));
        return $response; 
    }
    
    public function createResource(\Psr\Http\Message\ServerRequestInterface $request, \Psr\Http\Message\ResponseInterface $response): \Psr\Http\Message\ResponseInterface
    {
        /** @var \JSONAPI\Document\Document $document */
        $document = $request->getParsedBody();    
        $data = $document->getData();
        // ...data handling
        /** @var ObjectExample $createdObject */
        $createdObject;
        $document->setResource($createdObject);
        $response->getBody()->write(json_encode($document));
        return $response;
    }
}

```

> Response example
 
 ```json
{
    "jsonapi": {
        "version": "1.0"
    },
    "data": {
        "type": "resource",
        "id": "uuid",
        "attributes": {
            "publicProperty": "public-value",
            "privateProperty": "private-value"
        },
        "relationships": {
            "relations": {
                "data": [
                    {
                        "type": "resource-relation",
                        "id": "relation-uuid"
                    }
                ],
                "links": {
                    "self": "http://unit.test.org/resource/uuid/relationships/relations",
                    "related": "http://unit.test.org/resource/uuid/relations"
                }
            }
        },
        "links": {
            "self": "http://unit.test.org/resource/uuid"
        }
    },
    "included": [
        {
            "type": "resource-relation",
            "id": "relation-uuid",
            "relationships": {
                "object": {
                    "data": {
                        "type": "resource",
                        "id": "uuid"
                    },
                    "links": {
                        "self": "http://unit.test.org/resource-relation/relation-uuid/relationships/object",
                        "related": "http://unit.test.org/resource-relation/relation-uuid/object"
                    }
                }
            },
            "links": {
                "self": "http://unit.test.org/resource-relation/relation-uuid"
            }
        }
    ],
    "meta": {
        "count": 1
    }
}
```

## Issues

You can write [email](mailto://incoming+bednic-json-api-10827057-issue-@incoming.gitlab.com) or
create issue in [gitlab](https://gitlab.com/bednic/json-api/issues)
