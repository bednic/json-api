# Changelog

All notable changes to this project will be documented in this file.
The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

### [6.2.1](https://gitlab.com/bednic/json-api/compare/6.2.0...6.2.1) (2020-10-23)


### Changed

* **OAS:** Add examples to OAS <parameters> schemas ([caaef1b](https://gitlab.com/bednic/json-api/commit/caaef1bba506ce0762b48ea79a510c98036614c0))


### Fixed

* **Middleware:** POST request throws false exception ([c582c48](https://gitlab.com/bednic/json-api/commit/c582c48ccc4a86cdda4a90f91519f4a50b48e98d))

## [6.2.0](https://gitlab.com/bednic/json-api/compare/6.1.0...6.2.0) (2020-10-15)


### Added

* Add ResourceObject::hasAttribute & ResourceObject::hasRelationship ([701ba6d](https://gitlab.com/bednic/json-api/commit/701ba6d9a9cd7f9d22fa4fe532d464146b123905)), closes [#29](https://gitlab.com/bednic/json-api/issues/29)
* add several function to ExpressionBuilder itnerface ([2903901](https://gitlab.com/bednic/json-api/commit/290390177081dcd604d89dd7ca6b3f7d2150120f))


### Fixed

* Fix potential bugs ([e3ed9fd](https://gitlab.com/bednic/json-api/commit/e3ed9fd0a488cddc0b1d1e8e127d3abd6422e29c))
* request body getSize may return null ([97212ac](https://gitlab.com/bednic/json-api/commit/97212ac2ba5b08c87ceafac8c3be12bab14c7a83)), closes [#30](https://gitlab.com/bednic/json-api/issues/30)

## [6.1.0]

### Added
* Classes for JSON conversion
    * `JSONAPI\Document\Serializable`
    * `JSONAPI\Document\Deserializable`
    * `JSONAPI\Document\Convertible`

### Changed

### Deprecated

### Fixed

### Removed
* `bednic/tools` library, replaced with `symfony/string`


## [6.0.0]
This release bringing significant refactor, so please pay attention to changes. Whole usage should be simpler.
Most of the responsibility can be now on DI. Another advantage of changes is, that you can have more then one
metadata factories, so you can have several API groups under one application.

### Added
* `ErrorSource` class which wraps Error::source, implements additional information to Error,
like pointer, parameter or line of exception and trace
* `DocumentBuilderFactory` class which provides an easy setup of `DocumentBuilder`
via `::new(ServerRequestInterface $request)` method
* `InclusionFetcher` class which extract inclusion fetch logic outside of `DocumentBuilder`

### Changed
* Relationships now mustn't contains key data
* All relationships returning collection of resources now must return `Collection` interface.
Simple `array` was transferred internally anyway.
* Add InclusionInterface dependency to `Encoder::__construct`
* Renamed `UriPArtInterface` to `QueryPartInterface` to be more accurate
* `DocumentBuilder` was refactored to fit new use case
* Env `JSON_API_URL` is now at most cases `$baseURL` parameter in constructors, this should allow to
have different instances of JSON API in one project
* `LinkFactory` in now instantiable class with dependencies
* `PsrJsonApiMiddleware` now check incoming body against [schema](http://json-schema.org/draft-06/schema#)
    * there is known issue with *id* resource property, you have to send *id* even if you are creating resource

### Deprecated

### Fixed

### Removed
* `JsonSerializable`
* `JsonConvertible`
* `API_ENV_URL`
* `DocumentBuilder::create`


---
[Unreleased]: https://gitlab.com/bednic/json-api/compare/6.1.0...6.x
[6.1.0]: https://gitlab.com/bednic/json-api/compare/6.0.0...6.1.0
[6.0.0]: https://gitlab.com/bednic/json-api/compare/5.1.7...6.0.0
