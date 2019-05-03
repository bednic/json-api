# JSON API

This project goal is create easy-to-use library to implement JSON API specification.
It's accomplished by annotations.

Whole project is at the beginning of development. So don't hesitate to help.
I'm open to some good ideas how make this more customizable and friendly.

This library is tested in production with Doctrine2 and SlimPHP

This library only provides data and wrappers to create valid JSON API document. Controllers and Response is on you.


## Usage

> First we create some object and use annotations to define attributes

```php
<?php

use \JSONAPI\Annotation as API;

class AttributeOption {
    /**
    * @var Attribute 
    */
    private $attribute;
    
    public function setAttribute(Attribute $attribute){
        $this->attribute = $attribute;        
    }
}

/**
 * Class Attribute
 * @package IND\Model\Entity
 * @API\Resource("attributes")
 */
class Attribute
{
    /**
    * @var string 
    */
    private $label;
    
    /**
    * @var AttributeOption[]
    */
    private $options;
    
    /**
     * @API\Attribute
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }
    
    /**
     * @param string $label
     */
    public function setLabel(string $label): void
    {
        $this->label = $label;
    }
    /**
     * @API\Relationship(target=\AttributeOption::class)
     * @return AttributeOption[]
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @param AttributeOption[] $options
     */
    public function setOptions(array $options)
    {
        foreach ($options as $option) {
            $option->setAttribute($this);
        }
        $this->options = $options;
    }
}
``` 

> Then we need tu setup few things. Most of then belongs to DI Container

```php
<?php

// Create factory, best way is to do it through DI Container

$factory = new \JSONAPI\Metadata\MetadataFactory('/path/to/your/resources');

// Your object which you want to serialize
$attribute = new Attribute();

// Make Document instance
$document = new \JSONAPI\Document\Document($factory);

// Set your data
$document->setData($attribute);

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
        "type": "attributes",
        "id": 4501,
        "attributes": {
            "label": "(1) Label"
        },
        "relationships": {
            "options": {
                "data": [],
                "links": {
                    "self": "http://localhost/attributes/4501/relationships/options",
                    "related": "http://localhost/attributes/4501/options"
                }
            }
        }
    },
    "links": {
        "self": "http://localhost/attributes/4501"
    }
}
```

> To handle request data, you can use Document::createFromRequest to retrieve data, 
like Resources, then handle them in your model.

```php
<?php
/** @var \JSONAPI\Document\Document $document */
$document = \JSONAPI\Document\Document::createFromRequest(\Psr\Http\Message\RequestInterface $request);

/** @var \JSONAPI\Document\Resource|\JSONAPI\Document\Resource[] $resource */
$resource = $document->getData();

```

## Issues

You can write [email](mailto://incoming+bednic-json-api-10827057-issue-@incoming.gitlab.com) or
create issue in [gitlab](https://gitlab.com/bednic/json-api/issues)
