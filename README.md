# JSON API Mapper

Implemtation of [JSON API Standard Specification](https://jsonapi.org/)

This project goal is to create easy-to-use library to implement JSON API specification.

Whole project is at the beginning of development. So don't hesitate to help. I'm open to some good ideas how make this
more customizable and friendly.

Library only provides wrappers to create valid JSON API document. Controllers and Response is on you.

## Issues

You can write [email](mailto://incoming+bednic-json-api-10827057-issue-@incoming.gitlab.com) or create issue
in [gitlab](https://gitlab.com/bednic/json-api/issues)

## Installation

Install library via Composer

```
composer require bednic/json-api
```

## Basic Usage

> For simplicity, we use `$container` as some dependency provider (Dependency Injection).

### MetadataRepository

First we need create `MetadataRepository`. That we get from `MetadataFactory`.

```php
<?php
/** @var $container Psr\Container\ContainerInterface */
// This is cache instance implements PSR SimpleCache
$cache = $container->get( Psr\SimpleCache\CacheInterface::class);
// This is AnnotationDriver or SchemaDriver, depends on your preferences
$driver = $container->get(\JSONAPI\Driver\Driver::class);
// Paths to your object representing resources
$paths = ['paths/to/your/resources','another/path'];
// Factory returns instance of MetadataRepository
$repository = JSONAPI\Factory\MetadataFactory::create(
            $paths,
            $cache,
            $driver
        );

```

### DocumentBuilderFactory

#### Options

|Param|Default|Description|
|-----|-------|-----------|
|metadataRepository| |Instance of MetadataRepository.|
|baseURL| |URL where you API lays.|
|maxIncludedItems|625|Maximum items included in Compound Document. This prevents to compound huge documents.|
|relationshipLimit|25|Maximum items included in relationship collection.|
|relationshipData|true|Toggle if relationships should have data attribute.|
|supportInclusion|true|Toggle if server supports [inclusion](https://jsonapi.org/format/#fetching-includes).|
|supportSort|true|Toggle if server supports [sort](https://jsonapi.org/format/#fetching-sorting).|
|supportPagination|true|Toggle if server supports [pagination](https://jsonapi.org/format/#fetching-pagination).|
|paginationParser|null|PaginationParserInterface instance, which is responsible for parsing pagination.|
|filterParser|null|FilterParserInterface instance, which is responsible for parsing filter.|
|logger|null|LoggerInterface instance, PSR compliant logger instance.|

```php
<?php

// First we need DocumentBuilderFactory
// Let's get MetadataRepository from DI
/** @var $container Psr\Container\ContainerInterface */
$metadataRepository = $container->get(JSONAPI\Metadata\MetadataRepository::class);
$baseURL = "http://localhost/"; // base URL where API lays
// You can initialize it by yourself
$dbf = new JSONAPI\Factory\DocumentBuilderFactory($metadataRepository, $baseURL);
// OR you can let DI make it for you
$dbf = $container->get(JSONAPI\Factory\DocumentBuilderFactory::class);
// We need PSR ServerRequestInterface instance
/** @var  $request Psr\Http\Message\ServerRequestInterface */
// Here we create instance of JSONAPI\Document\Builder
$builder = $dbf->new($request);
// Your object|objects
$data = new MyObject();
// $doc here is Document instance
$doc = $builder->setData($data)->build();

```

Now if you encode `$doc` to JSON, you're going to get full JSON API Document. More information about UriParser
is [here](UriParser)

### Describing your objects

You can choose which way you want to describe your object metadata.

#### With Annotations

> Note: If you want to use annotations you have to use `AnnotationDriver` in `MetadataFactory`

### [Example](tests-resources/valid/GettersExample.php)

How you can see, setting up resource object is quiet easy. Just annotate your getter with ``#[Attribute]``
or ``#[Relationship]`` annotation.

#### Schema

The important part is to implement Resource interface. Then fill up static method `getSchema`.

> Note: If you want to use schema you have to use `SchemaDriver` in `MetadataFactory`

### [Example](tests-resources/valid/PropsExample.php)

## OAS

### Basic Example

```php
    $factory = new OpenAPISpecificationBuilder(self::$mr);

    $info = new Info('JSON:API OAS', '1.0.0');
    $info->setDescription('Test specification');
    $info->setContact(
        (new Contact())
            ->setName('Tomas Benedikt')
            ->setEmail('tomas.benedikt@gmail.com')
            ->setUrl('https://gitlab.com/bednic')
    );
    $info->setLicense(
        (new License('MIT'))
            ->setUrl('https://gitlab.com/bednic/json-api/-/blob/5.x/LICENSE')
    );
    $info->setTermsOfService('https://gitlab.com/bednic/json-api/-/blob/5.x/CONTRIBUTING.md');

    $oas = $factory->create($info);
    $oas->setExternalDocs(new ExternalDocumentation('https://gitlab.com/bednic/json-api/-/wikis/home'));

    $json = json_encode($oas);
```

## UriParser

This object works with url, and parse required keywors as described at JSON API Standard

### Options

|Param|Default|Description|
|-----|-------|-----------|
|request| |Instance of PSR compliant ServerRequestInterface.|
|metadataRepository| |Instance of MetadataRepository.|
|baseURL| |URL where you API lays.|
|supportInclusion|true|Toggle if server supports [inclusion](https://jsonapi.org/format/#fetching-includes).|
|supportSort|true|Toggle if server supports [sort](https://jsonapi.org/format/#fetching-sorting).|
|supportPagination|true|Toggle if server supports [pagination](https://jsonapi.org/format/#fetching-pagination).|
|paginationParser|null|PaginationParserInterface instance, which is responsible for parsing pagination.|
|filterParser|null|FilterParserInterface instance, which is responsible for parsing filter.|
|logger|null|LoggerInterface instance, PSR compliant logger instance.|

### PathInterface

Provides information about path, like resource type, resource ID, relation type, is it collection or is it relationship

### Fieldset

https://jsonapi.org/format/#fetching-sparse-fieldsets

### Filter

https://jsonapi.org/format/#fetching-filtering

As described, specification is agnostic about filter implementation. So I created, more like borrowed, expression filter
from OData. So now you can use something like this:

`filter=stringProperty eq 'string' and contains(stringProperty,'asdf') and intProperty in (1,2,3) or boolProperty ne true and relation.property eq null`

There are for now two `ExpressionBuilder`s:

* `DoctrineQueryExpressionBuilder` creates expression for Doctrine QueryBuilder, it comes hadny if you are using
  Doctrine and want more complex filtering
* `DoctrineCriteriaExpressionBuilder` works well with ArrayCollection or PersistentCollection which is dependency of
  this library so it's default and you can use it too. But it provides only some basic expressions like eq, ne, etc...
  But you can't filter by relation property or use some functions, or expressions.
* `ClosureExpressionBuilder` create expression tree from closures, so you do not need doctrine at all.

### Pagination

https://jsonapi.org/format/#fetching-pagination

I implement two of three pagination technics

* LimitOffsetPagination
* PagePagination
* CursorPagination which is only abstract, cause it need more then just several number, so if you want use cursor based
  pagination, you have to implement it by yourself.

### Includes

https://jsonapi.org/format/#fetching-includes

### Sort

https://jsonapi.org/format/#fetching-sorting