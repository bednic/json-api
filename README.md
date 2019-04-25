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
    * @var \AttributeOption
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
     * @return \AttributeOption[]
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @param \AttributeOption[] $options
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

$factory = new \JSONAPI\MetadataFactory('/path/to/your/resources');

// Getting metadata
$metadata = $factory->getMetadataByClass(\Attribute::class);

// Bellow you can see, you have access to all necessary data
$resourceType = $metadata->getResource()->type;
$relationships = $metadata->getRelationships();
$attributes = $metadata->getAttributes();

// Best use it by DI Container, its necessary to right links creation
$linkProvider = new \JSONAPI\LinkProvider('http://localhost/');

MetaEncoder
$encoder = new \JSONAPI\Encoder($factory, $linkProvider);

// Our object
$attribute = new \Attribute();

$resourceIdentifier = $encoder($attribute)->encode();
// OR
$resourceIdentifier = $encoder->create($attribute)->encode();

$resource = $encoder->create($attribute)->withFields()->encode();
// OR
$resource = $encoder($attribute)->withFields()->encode();

// At the end we create Document
$document = new \JSONAPI\Document\Document($linkProvider);
// Setup resource data
$document->setData($resource);
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

## Issues

You can write [email](mailto://incoming+bednic-json-api-10827057-issue-@incoming.gitlab.com) or
create issue in [gitlab](https://gitlab.com/bednic/json-api/issues)
