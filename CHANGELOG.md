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
[Unreleased]: https://gitlab.com/bednic/json-api/compare/2.3.1...2.x
[2.3.0]: https://gitlab.com/bednic/json-api/compare/2.3.0...2.3.1
[2.3.0]: https://gitlab.com/bednic/json-api/compare/2.2.1...2.3.0
[2.2.1]: https://gitlab.com/bednic/json-api/compare/2.2.0...2.2.1
[2.2.0]: https://gitlab.com/bednic/json-api/compare/2.1.1...2.2.0
[2.1.1]: https://gitlab.com/bednic/json-api/compare/2.1.0...2.1.1
[2.1.0]: https://gitlab.com/bednic/json-api/compare/2.0.0...2.1.0
[2.0.0]: https://gitlab.com/bednic/json-api/compare/2.0.0...2.0.0
