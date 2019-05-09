# Changelog

All notable changes to this project will be documented in this file.
The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
* LinkProvider::createRelatedLink() create related resource link
* LinkProvider::createSelfLink() create self link 

### Changed
* DocumentException::PRIMARY_DATA_TYPE_MISMATCH to ::RESOURCE_TYPE_MISMATCH
* DocumentException::FORBIDDEN_VALUE_TYPE to ::FORBIDDEN_DATA_TYPE

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
[Unreleased]: https://gitlab.com/bednic/json-api/compare/2.0.0...2.x
[2.0.0]: https://gitlab.com/bednic/json-api/compare/2.0.0...2.0.0
