# Changelog

All notable changes to this project will be documented in this file.
The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).


## [Unreleased]

### Added

### Changed

### Deprecated

### Fixed

### Removed


## [5.1.5]

### Added
* tests for LinkFactory

### Changed

### Deprecated

### Fixed
* fix bad url parsing when server is using proxy

### Removed


## [5.1.4]

### Added

### Changed

### Deprecated

### Fixed
* default condition value cannot be Criteria, changed to null to prevent uninitialized error

### Removed


## [5.1.3]

### Added

### Changed

### Deprecated
* `JsonConvertible` replaced by `\Tools\JSON\JsonConvertible`
* `JsonDeserializable` replaced by `\Tools\JSON\JsonDeserializable`

### Fixed
* add exception to logger context in middleware catch exception

### Removed


## [5.1.2]

### Added

### Changed

### Deprecated

### Fixed
* fixed [issue 26](https://gitlab.com/bednic/json-api/-/issues/26)

### Removed


## [5.1.1]

### Added

### Changed
* Add logger param in `OpenAPISpecificationBuilder` constructor
* New method `Document::setJSONAPIObjectMeta` setting meta to jsonapi object
* Changed method signature in `OpenAPISpecificationBuilder::createCreateResponses`

### Deprecated

### Fixed
* fix missing relationship meta information
* fix several OAS missing implementation constraints

### Removed


## [5.1.0]

### Added
* static `UriParser::paginationEnabled` enables pagination
* [Generate API schema](https://gitlab.com/bednic/json-api/-/issues/6)
    * see [Builder class](/src/OAS/Factory/OpenAPISpecificationBuilder.php) for examples
    * for usage see [docs](https://gitlab.com/bednic/json-api/-/wikis/OAS-Usage)

### Changed

### Deprecated

### Fixed
* Fixed [issue 23](https://gitlab.com/bednic/json-api/-/issues/23)
* Fixed inclusion as described in docs
    > Note: Because compound documents require full linkage (except when relationship linkage is excluded by sparse
    fieldsets), intermediate resources in a multi-part path must be returned along with the leaf nodes. For example,
    a response to a request for comments.author should include comments as well as the author of each of those comments.

### Removed


## [5.0.0]

### Added

### Changed
* `ExpressionBuilder`
    * `::neq` changed to `::ne`
    * `::lte` changed to `::le`
    * `::gte` changed to `::ge`
* `UseDottedIdentifier::parseIdentifier` removed return type string

### Deprecated

### Fixed
* `ExpressionFilterParse::parseInteger` did not return literal expression
* `ExpressionFilterParse::parseDatetime` did not pass `DateTime` object to `ExpressionBuilder::literal`

### Removed


## [4.2.4]

### Added

### Changed

### Deprecated

### Fixed
* Fix path parsing when base path contains additional path

### Removed


## [4.2.3]

### Added

### Changed

### Deprecated

### Fixed
* Fix `Metadata\Relationship` static creation bad default values in `$isCollection` arg

### Removed


## [4.2.2]

### Added

### Changed

### Deprecated

### Fixed
* Fix Schema driver bug, when it uses reflection from schema provider, not from metadata className

### Removed


## [4.2.1]

### Added

### Changed

### Deprecated

### Fixed
* [PsrJsonApiMiddleware fails because Error is json-unencodable](https://gitlab.com/bednic/json-api/-/issues/17)
* [Error class instantiation fails with non-integer exception code](https://gitlab.com/bednic/json-api/-/issues/16)

### Removed


## [4.2.0]

### Added
* `ExpressionFilterParser` improved expression parser. More 'OData like'. Now supports own expression building.
* `ExpressionBuilder` interface, by this interface you can serve won expression builder ant thus create own filtering
 strategy, based on your database library.
* `DoctrineCriteriaExpressionBuilder` is wrapper for `Doctrine\Collection\ExpressionBuilder` and it's default builder.
* `DoctrineQueryExpressionBuilder` is wrapper for `Doctrine\ORM\Query\Epxr`, require Doctrine ORM installed.

### Changed
* `MetadataFacory` now doesn't throw `\Psr\SimpleCache\InvalidArgumentException` when invalid class name is served.
 Instead, it throws `ClassNotExist` exception.
* `UriParser::getPrimaryResourceType`, `UriParser::isCollection` moved to `PathInterface`

### Deprecated

### Fixed
* Inclusion parsing and inclusion fetching

### Removed
* `ResourceCollection` type check, in case of inheritance this was unnecessary behavior. So I remove it for good.
If you don't use `ResourceCollection` manually, just don't care.
* `CriteriaFilterParser` replaced by `ExpressionFilterParser`, and `::getCondition()` now returns comparison
instead Criteria
* `UriParser::getRelationshipType` not used anywhere

## [4.1.1]

### Added

### Changed

### Deprecated

### Fixed
* body parsing in `PsrJsonApiMiddleware`, add `::rewind` before reading body stream.

### Removed

### Security

## [4.1.0]

### Added

### Changed

### Deprecated
* environment variable `JSON_API_URL`, now use LinkFactory::$ENDPOINT = 'http://your.api.com/endpoint'

### Fixed
* `PsrJsonApiMiddleware` implementation, it rely on php://input which is not right way

### Removed


### Security

## [4.0.0]
This version brings new big feature and that is Schema driven metadata mapping. So now, if you don't like
`Doctrine\Annotations`, you can use another technique how to map class metadata.
Some things change radically to achieve more strict way to work with several data domains, like URI, data from request,
JSON-API Document itself. I focused to bring more freedom for work with Document. But you can still
use DocumentBuilder which works similar like Document before. But now you can easily create Document by yourself just
with Encoder and MetadataRepository.

In future I'll focus to separate inclusion fetching from DocumentBuilder to make possible use it out of box.
Next great feature will be OpenAPI schema generation and if all goes well, improving CriteriaFilterParser.

I'll write some examples and use cases into Wiki later. For now if you want to see how to use it look
at [test-resources](/tests-resources/valid)

### Added
* `DocumentBuilder` builder for creating Documents. This is probably temporary solution before
decomposing inclusion parsing
* `ResourceCollection` for handling collection of resources, can by typed for primary data,
or non-typed for included resources
* Schema Driven mapping, so now you can define class metadata without annotations,
all smart features such as type guessing, setter guessing etc. still works
    * `Schema\Resource` interface marks object as resource
    * `Schema\ResourceSchema` class works as DTO (Data Transfer Object) for metadata
    * `SchemaDriver`
* `UriParser` class, contains all URI handlers, such as filter, pagination, sort etc
* `PsrJsonApiMiddleware` no returns `Document` as parsed body, but only if request method can have content
* `PrimaryData` interface mark object as primary data, it can be Resource, ResourceIdentifier
or Collection of Resources or ResourceIdentifiers
* `MetadataRepository` contains storing techniques from `MetadataFactory`

### Changed
* `Filter` and `Pagination` interface moved to own namespaces
* `LinkFactory` now works as filler [see](src/Uri/LinkFactory.php)
* `MetadataFactory` now works as factory only and generate `MetadataRepository`

### Deprecated
* `Document::getPagination` moved to `UriParser`
* `Document::getFilter` moved to `UriParser`
* `Document::getEncoder` now you must create encoder by yourself

### Fixed

### Removed
* `UriParser` interface

### Security

## [3.1.1]

### Added
* `ReserveWordException` thrown when Attribute/Relation name is `type` or `id`.

### Changed

### Deprecated

### Fixed
* It was possible have Attribute or Relationship named as `type` or `id`. Now it's fixed.
* Fix getter detection. Return type of method is now optional, not required.

### Removed

### Security

## [3.1.0]

### Added
* JSON API schema validation for tests
* `CriteriaFilterParser` advanced filter inspired by OData semantic
* `Annotation\Attribute::of` property, which is used for declare type of array items.
This will be used primary for OpenAPI schema
* `ClassMetadata::getShortClassName` return class short name
* `Annotation\Meta` is used for meta information of ResourceObject
* `Annotation\Attribute::isProperty` returns true if Annotation is on property
* `Annotation\Attribute::isReadOnly` returns true if Attribute is read-only
* `CriteriaFilterParserTest`

### Changed
* Now we can return Relationships collections as `Doctrine\Common\Collections\Collection` (preferred)
  or as `array` (old way)
* Determination of attribute data type is now resolved in sequence:
    1. Try get type from return type of function
    2. Get type from setter parameter, if setter exists
* `ClassMetadata::__construct` now accept reflection of class as first argument, instead string className
* Now if you want to make attribute read-only, just type `setter=false` instead `setter=""`, but both solutions are ok.

### Deprecated

### Fixed
* `AnnotationDriver` inconsistencies. There is rule, that if `Annotation\Attribute` is on getter method,
  then `Attribute::proptery` is empty, so we can decide if we work with object property, or object getter.
  At some points of algorithm this rule was broken.

### Removed

### Security

## [3.0.1]

### Added

### Changed
* `JsonDeserializable::jsonDeserialize` is now type free, cause sometimes we need serialize non-array data

### Deprecated

### Fixed

### Removed

### Security

## [3.0.0]

### Added
* `Filter` interface
    * This resolve problem with externally defined filter parser. Because JSON-API standard is agnostic
    about filtering, we should be too. So now it's possible define own parser which will parse filter value
* `Pagination` interface
    * This interface is focused to extract pagination strategy out of library. Cause JSON-API is agnostic
    about pagination strategy. So you can now define own strategy for pagination. Now we support page-based,
    offset-based and cursor-based, but all only throw interface. Only offset-based strategy is implemented

* `LimitOffsetPaginationParser` offset-based pagination implementation
* `VoidFilterParser` default filter parser, just pass filter without modification
* `MethodNotImplemented` is supposed to be thrown when method in `Filter` or `Pagination` is not implemented
it's standard server error, not uri error.
* `Encoder::relationshipLimit` this tells to encoder to limit relationships, so it's not that huge on response
* `Document::getEncoder()` expose `Encoder` out
* `Encoder::setRelationshipLimit()` through this you can set limit for relationships
* `Encoder::getRelationshipLimit()`

### Changed
* `Query::getFilter` now returns `Filter` interface
* `Query::getPagination` now returns `Pagination` interface

### Deprecated

### Fixed
* Primary link now contains query string, like ?filter etc.

### Removed
* Default filtering is removed, see `Filter` interface

### Security

## [2.4.0]

### Added
* <code>JsonDeserializable</code> interface - marks object as json deserializable,
  so it's possible create instance of object from plain json
* <code>JsonConvertible</code> interface - merge JsonSerializable and JsonDeserializable together.

### Changed
* JSONAPI\Utils\LinksImpl => JSONAPI\LinksTrait
* JSONAPI\Utils\MetaImpl => JSONAPI\MetaTrait

### Deprecated

### Fixed

### Removed
* JSONAPI\Utils

### Security

## [2.3.4]

### Added

### Changed

### Deprecated

### Fixed
* AnnotationDriver::isGetter return bad state if getter return same class as is caller class.
  Historical reason cause fluent setters. Now check removed, cause Parent->Children object relations.

### Removed

### Security

## [2.3.3]

### Added

### Changed

### Deprecated

### Fixed
* fix issue [#9](https://gitlab.com/bednic/json-api/issues/9)

### Removed

### Security

## [2.3.2]

### Added

### Changed

### Deprecated

### Fixed
* JsonApiMiddleware throw UnsupportedMediaType even for get, when it's not necessary.
  Now middleware check Content-Type only when it's POST or PATCH, so body is expected.

### Removed

### Security

## [2.3.1]

### Added

### Changed

### Deprecated

### Fixed
* URL parsing bug, don't recognize camelCase member names
* Fix Document::setData bad behaviour on empty data

### Removed

### Security

## [2.3.0]

### Added
* Encoder::identify return ResourceObjectIdentifier
* ClassMetadata::getClassName return name of class of metadata

### Changed

### Deprecated

### Fixed
* Fix Document::isCollection bad resolving
* Fix Driver, now only property annotations has Annotation::property non-empty.
  So when is Annotation::property filled up, then it means  it is property annotation, but if you annotation method then
  it get privilege and will be used. In standard case it means, that if is Annotation::setter filled up, then
  annotations are on methods, if Annotation::property is filled up then annotations are  on props.
* Some url bugs, most typos
* Relationships returns bad data format
* Primary data links when relationships are returned
* Request data parsing, when ResourceObjectIdentifiers are provided

### Removed
* QueryFactory

### Security

## [2.2.1]

### Added

### Changed

### Deprecated

### Fixed
* Request does not contain json body. Added parsing to PsrJsonApiMiddleware.
* Replace RequestInterface to ServerRequestInterface in Document::createFromRequest.
* Fix return type from ResourceIdentifier::getId() should be always string or null;
* Fixed README

### Removed

### Security

## [2.2.0]

### Added

### Changed
* Whole error handling is reworked. Idea is that BadRequest exception are sent to user. But other JsonApiExceptions
  are Internal Server Error, so you should handle them by self. Exceptions are categorised by purpose. [See](/src/Exception)
* PsrJsonApiMiddleware now send PSR7 compatible Response with valid Document contains information about BadRequest error
  in case of exception.
* Query::path is now private and you can access it by getter ::getPath().

### Deprecated

### Fixed

### Removed

### Security

## [2.1.1]

### Added
* HttpException, NotFoundException for handle HTTP errors
* Slim\Psr7 library for handling JSON API implementation
  requirement around headers and HTTP responses, like NotFound, BadRequest, UnsupportedMediaType etc...

### Changed
* UnsupportedMediaTypeException now inherit from HttpException

### Deprecated

### Fixed
* Some enchantments to Query class to fix some vulnerabilities

### Removed

### Security

## [2.1.0]

### Added
* LinkProvider::createRelatedLink() create related resource link
* LinkProvider::createSelfLink() create self link

### Changed
* DocumentException::PRIMARY_DATA_TYPE_MISMATCH to ::RESOURCE_TYPE_MISMATCH
* DocumentException::FORBIDDEN_VALUE_TYPE to ::FORBIDDEN_DATA_TYPE
* API_ENV_URL to JSON_API_URL

### Deprecated

### Fixed
* Query ignores URL with query part
* Fixed some linkage mistakes

### Removed

### Security

## [2.0.0]

### Added
* Document::createFromRequest to handle incoming data
* Path, helper object which can determine what is primary data type
* class QueryFactory, use it for getting instance of Query
* class Meta, meta wrapper
* class Link, link wrapper
* QueryException

### Changed
* Document::setData() now accept object/objects instead of Resource|Resource[]
* class Filter is now class Query and contains Query::path, instance of Path
* Document::setMeta(Meta $meta) function signature now accept Meta object
* Document::setLink(Link $link) function signature now accept Link object
* class LinkProvider is now member of \JSONAPI\Query namespace
* Error::fromException(JsonApiException $exception) replace __constructor(\Throwable) and accept only JsonApiException class
* ::getPrimaryDataType() moved from Query\Path to Document
* Annotation Common::setter now accept only string, cause boolean is not necessary.
  If you want disable setter, just use empty string
* Path::__construct() now throw exception if you set relationships and related at same time.

### Deprecated

### Fixed
* LinkProvider::createPrimaryDataLink(), returns bad uri, when new resource was created
* Path::__toString(), return bad link, when relationships is as primary data

### Removed
* Document::create()
* Document::getIncludes()
* Document::setIncludes()
* Document::getLink()
* Document::getMeta()
* class SlimJsonApiMiddleware, future version of slim no longer supports this signature of middleware.

### Security

---
[Unreleased]: https://gitlab.com/bednic/json-api/compare/5.1.5...5.x
[5.1.5]: https://gitlab.com/bednic/json-api/compare/5.1.4...5.1.5
[5.1.4]: https://gitlab.com/bednic/json-api/compare/5.1.3...5.1.4
[5.1.3]: https://gitlab.com/bednic/json-api/compare/5.1.2...5.1.3
[5.1.2]: https://gitlab.com/bednic/json-api/compare/5.1.1...5.1.2
[5.1.1]: https://gitlab.com/bednic/json-api/compare/5.1.0...5.1.1
[5.1.0]: https://gitlab.com/bednic/json-api/compare/5.0.0...5.1.0
[5.0.0]: https://gitlab.com/bednic/json-api/compare/4.2.4...5.0.0
[4.2.4]: https://gitlab.com/bednic/json-api/compare/4.2.3...4.2.4
[4.2.3]: https://gitlab.com/bednic/json-api/compare/4.2.2...4.2.3
[4.2.2]: https://gitlab.com/bednic/json-api/compare/4.2.1...4.2.2
[4.2.1]: https://gitlab.com/bednic/json-api/compare/4.2.0...4.2.1
[4.2.0]: https://gitlab.com/bednic/json-api/compare/4.1.1...4.2.0
[4.1.1]: https://gitlab.com/bednic/json-api/compare/4.1.0...4.1.1
[4.1.0]: https://gitlab.com/bednic/json-api/compare/4.0.0...4.1.0
[4.0.0]: https://gitlab.com/bednic/json-api/compare/3.1.1...4.0.0
[3.1.0]: https://gitlab.com/bednic/json-api/compare/3.1.0...3.1.1
[3.1.0]: https://gitlab.com/bednic/json-api/compare/3.0.1...3.1.0
[3.0.1]: https://gitlab.com/bednic/json-api/compare/3.0.0...3.0.1
[3.0.0]: https://gitlab.com/bednic/json-api/compare/2.4.0...3.0.0
[2.4.0]: https://gitlab.com/bednic/json-api/compare/2.3.4...2.4.0
[2.3.4]: https://gitlab.com/bednic/json-api/compare/2.3.3...2.3.4
[2.3.3]: https://gitlab.com/bednic/json-api/compare/2.3.2...2.3.3
[2.3.2]: https://gitlab.com/bednic/json-api/compare/2.3.1...2.3.2
[2.3.1]: https://gitlab.com/bednic/json-api/compare/2.3.0...2.3.1
[2.3.0]: https://gitlab.com/bednic/json-api/compare/2.2.1...2.3.0
[2.2.1]: https://gitlab.com/bednic/json-api/compare/2.2.0...2.2.1
[2.2.0]: https://gitlab.com/bednic/json-api/compare/2.1.1...2.2.0
[2.1.1]: https://gitlab.com/bednic/json-api/compare/2.1.0...2.1.1
[2.1.0]: https://gitlab.com/bednic/json-api/compare/2.0.0...2.1.0
[2.0.0]: https://gitlab.com/bednic/json-api/compare/2.0.0...2.0.0