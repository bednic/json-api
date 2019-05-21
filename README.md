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
/**
 * Created by IntelliJ IDEA.
 * User: tomas
 * Date: 24.04.2019
 * Time: 12:47
 */

namespace App;

// Don't forget use Annotations, otherwise you get exception
use \JSONAPI\Annotation as API;

/*
 * Define some objects
 */

/**
 * Class Common
 *
 * @package JSONAPI
 */
abstract class Common
{

    /**
     * @var string
     */
    protected $id;

    /**
     * @API\Id
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
 * @package App
 * @API\Resource("resource")
 */
class ObjectExample extends Common
{
    protected $id = 'uuid';
    /**
     * @API\Attribute
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
     * @API\Attribute
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
     * @API\Attribute(setter="")
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
 * @package App
 * @API\Resource("resource-relation")
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

/*
 *  Then we need setup few things
 */

// Create factory, best way is to do it through DI Container
$factory = new \JSONAPI\Metadata\MetadataFactory('/path/to/your/resources');

// Your object which you want to serialize
$object = new \App\ObjectExample();

// Make Document instance
$document = new \JSONAPI\Document\Document($factory);

// Set your data
$document->setData($object);

// Your HTTP Response 
$response->sendJson($document);
```

> Response 
 
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
    "links": {
        "self": "http://unit.test.org/resource/uuid"
    },
    "meta": {
        "count": 1
    }
}
```

> To handle request data, you can use Document::createFromRequest to retrieve data, 
like Resources, then handle them in your model.

```php
<?php
/** @var \JSONAPI\Document\Document $document */
$document = \JSONAPI\Document\Document::createFromRequest(
    \Psr\Http\Message\ServerRequestInterface $request,
 \JSONAPI\Metadata\MetadataFactory $factory
);

/** @var \JSONAPI\Document\Resource|\JSONAPI\Document\Resource[] $resource */
$resource = $document->getData();

```
> A little help with handling request/response you can use PsrJsonApiMiddleware. 
Which provides header check for right content-type and parsing json body to ServerRequestInterface::parsedBody.
Furthermore consume BadRequest exception and return 4xx based on exception to client. 
It's compatible with PSR7 standard.

```php
<?php
$middleware = new \JSONAPI\Middleware\PsrJsonApiMiddleware(
    \JSONAPI\Metadata\MetadataFactory $factory,
    \Psr\Log\LoggerInterface $logger
    
);
$route->add($middleware);
```

## Exception Handling

For now, it takes care of exceptions inherited from BadRequest. More fatal exception which are usually equal 
Server Internal Error are not consumed by middleware. It's on you to handle these type of exception. Every exception 
thrown by this library are inherited from JsonApiException and considered like Server Internal Error so ::getStatus 
return 500. You can consume these exception and still send valid JSON API Document, just use 
Error::fromException(JsonApiException $exception), or create own Error instance and set all useful data. Then you can add
errors to Document instance ::addError() and send it to client. This belong to error handlers and as there isn't PSR 
standard for exception handlers, you have to do it by self. 

### Example
```php
<?php
// Some exception
$exception = new \JSONAPI\Exception\Encoder\EncoderException("Bad thing happened");

// You need factory instance for document
$factory = new \JSONAPI\Metadata\MetadataFactory('/resources');

// Create new Document
$document = new \JSONAPI\Document\Document($factory);

// Create Error from exception
$error = \JSONAPI\Document\Error::fromException($exception);

// Set error to document
$document->addError($error);

// Or create own Error
$myOwnError = new \JSONAPI\Document\Error();

// Set some useful information
$myOwnError->setTitle("Bad day");
$myOwnError->setDetail("Someone split my coffee!");

// You can add multiple errors to Document
$document->addError($myOwnError);

// Send it to client
$response->sendJson($document);
```

## Issues

You can write [email](mailto://incoming+bednic-json-api-10827057-issue-@incoming.gitlab.com) or
create issue in [gitlab](https://gitlab.com/bednic/json-api/issues)
