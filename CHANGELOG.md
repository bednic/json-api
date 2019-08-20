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
[Unreleased]: https://gitlab.com/bednic/json-api/compare/3.0.1...3.x
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
