# Changelog

All notable changes to this project will be documented in this file.
The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [6.4.0](https://gitlab.com/bednic/json-api/compare/6.3.1...6.4.0) (2020-11-13)


### Added

* **Document:** Error factory ([ca6d011](https://gitlab.com/bednic/json-api/commit/ca6d01119edc8ae6744116c38901d4c130c9c9be))


### Fixed

* **SortParser::parse incompatible behavior:** clrf ([fad6240](https://gitlab.com/bednic/json-api/commit/fad6240842001bcb5d96bd200dd84229be57627b)), closes [#33](https://gitlab.com/bednic/json-api/issues/33)
* **Uri:** insufficient parsers behavior ([6657dea](https://gitlab.com/bednic/json-api/commit/6657deacd5479848246a5f0366a492bf054c66aa))
* **Uri:** LimitOffsetPagination const instead strings ([e51aa6d](https://gitlab.com/bednic/json-api/commit/e51aa6df21be4d4df15327f60f85ddcb8b7f4acc))
* **Uri:** PathParser ([423e4fb](https://gitlab.com/bednic/json-api/commit/423e4fbb207c749550e6f930848fd2def32be7df))
* **Uri:** SortParser behavior ([bc02bdc](https://gitlab.com/bednic/json-api/commit/bc02bdce445f26aee46d70e5455ae6b8bdbe9021))
* **Uri:** SortParser regex ([3ee4457](https://gitlab.com/bednic/json-api/commit/3ee44570e0ce0e7034b5694be3b8445332306e8a))
* **Uri:** SortParser::parse ([81cc097](https://gitlab.com/bednic/json-api/commit/81cc0978f814522f2234586be59e1f16e9e8d30b))
* PathParser::parse condition mistake ([fb394f7](https://gitlab.com/bednic/json-api/commit/fb394f791399b30445695e20a60dc4894359eed0))

### [6.3.1](https://gitlab.com/bednic/json-api/compare/6.3.0...6.3.1) (2020-10-26)


### Fixed

* Meta didnt accept very short prop names ([5c89997](https://gitlab.com/bednic/json-api/commit/5c899979c6d04e44af47b77d00b05f4e64e2f539))

## [6.3.0](https://gitlab.com/bednic/json-api/compare/6.2.3...6.3.0) (2020-10-26)


### Added

* ResourceCollection::toArray ([b8ef68c](https://gitlab.com/bednic/json-api/commit/b8ef68c52c2509a2a37524932eec2b3bcbe3f805))


### Changed

* Change constructor ([dd7119d](https://gitlab.com/bednic/json-api/commit/dd7119df06c76aa1246187ff19705eaf23f11a25))

### [6.2.3](https://gitlab.com/bednic/json-api/compare/6.2.2...6.2.3) (2020-10-25)


### Changed

* Resource::type is now not required ([2ab1b15](https://gitlab.com/bednic/json-api/commit/2ab1b1554f11155aefa9703df91736ac726e25a1))

### [6.2.2](https://gitlab.com/bednic/json-api/compare/6.2.1...6.2.2) (2020-10-24)


### Fixed

* **Middleware:** add body rewind after reading contents ([923aee8](https://gitlab.com/bednic/json-api/commit/923aee80ddbbbada1d24c94c8d510f291f6fb9ef))

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
