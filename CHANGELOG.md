# Changelog

All notable changes to this project will be documented in this file.
The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

### [7.1.1](https://gitlab.com/bednic/json-api/compare/7.1.0...7.1.1) (2021-02-09)


### Fixed

* **OAS:** Bad error document response description ([63222cd](https://gitlab.com/bednic/json-api/commit/63222cd4d43adbb04036bfe7456b22c1efc8741b))
* Fix sort parsing ([e743c6d](https://gitlab.com/bednic/json-api/commit/e743c6d17b7c0d16ffd5f967b8b3756315806b99))

## [7.1.0](https://gitlab.com/bednic/json-api/compare/7.0.0...7.1.0) (2021-01-24)


### Fixed

* bad class instance in slice method ([133b0bc](https://gitlab.com/bednic/json-api/commit/133b0bc34969b8f4f3bfdcfa023869ca50a1639e))
* fix has implementation ([b99d823](https://gitlab.com/bednic/json-api/commit/b99d823c83b51ed316630dd13d1105d021fff63b))
* Change default filter expression builder ([05f7d63](https://gitlab.com/bednic/json-api/commit/05f7d63f9149eb6d68903cc1a9deaac5b21ba89e))
* Method isCollection bad behavior ([5323dbb](https://gitlab.com/bednic/json-api/commit/5323dbb48b5792c40f3575870b889643332f869e))
* Relationship::getData return type ([ec2729e](https://gitlab.com/bednic/json-api/commit/ec2729e3d717a6a9a2837f43f2168bd9d3f06b1a))
* wrong parameters passed to parent ctor in Relationship annotation ([d29095f](https://gitlab.com/bednic/json-api/commit/d29095f87e068a3e35abae6c009213b8d13b0aef))


### Added

* Add has filter function ([eb7d3d8](https://gitlab.com/bednic/json-api/commit/eb7d3d823968e6524b223ade287fc8965597f4dd))
* Add sort functions to Collection ([ba3a214](https://gitlab.com/bednic/json-api/commit/ba3a2140354efec439ac6a94f374328308360c70))

## [7.0.0](https://gitlab.com/bednic/json-api/compare/7.0.0-0...7.0.0) (2021-01-17)

## [7.0.0-0](https://gitlab.com/bednic/json-api/compare/6.4.0...7.0.0-0) (2020-12-14)


### ⚠ BREAKING CHANGES

* PHP8

### Changed

* **docs:** Move DocumentBuilder to Document\Builder ([38272a7](https://gitlab.com/bednic/json-api/commit/38272a7764a689feeabe919b2e8ba6a197603c25))
* **Document:** Move helper traits to Document namespace ([daaaf07](https://gitlab.com/bednic/json-api/commit/daaaf073988ed4299c146fa78e5557c2d0061eb4))
* **Exception:** Move OAS exceptions to Exception namespace ([b2f6162](https://gitlab.com/bednic/json-api/commit/b2f6162d26b25269f4960a272278f0ea9714d891))
* **Factory:** Wrap factories to separate namespace Factory ([56e6670](https://gitlab.com/bednic/json-api/commit/56e6670cc13106dbf2ca00af89ef6f5b30de7d6e))
* **OAS:** Rename OAS\Enum to OAS\Type ([589cd7d](https://gitlab.com/bednic/json-api/commit/589cd7d5506c7ea72e7f394d0e39e8d55a37b77f))
* Rename Uri namespace to URI ([0a8f6aa](https://gitlab.com/bednic/json-api/commit/0a8f6aa6064525fb6fefd6455ced31a90c537344))


### Fixed

* Revert removing NotFound exception ([74f134e](https://gitlab.com/bednic/json-api/commit/74f134e8ff1a4f8ec2959d1dbdee634cb9dd07f6))
* **Middleware:** Errors was not logged by logger ([cfd7e20](https://gitlab.com/bednic/json-api/commit/cfd7e200018d673833778907e9a9c77cc951f7d4))


### Added

* Migrate to PHP8 ([ddf9b76](https://gitlab.com/bednic/json-api/commit/ddf9b7670d446ed08a75a0542867682a5d85bf8d))
* **Collection:** Create own Collection class ([80fd582](https://gitlab.com/bednic/json-api/commit/80fd582b4fc570825d53ac635c9c3eb41175032c))
* **Helper:** Add DoctrineCollectionAdapter ([e5a114d](https://gitlab.com/bednic/json-api/commit/e5a114d8611ad2344de29ed177cc930487c8de02))

## [6.4.0](https://gitlab.com/bednic/json-api/compare/6.3.1...6.4.0) (2020-11-13)


### Added

* **Document:** Error factory ([ca6d011](https://gitlab.com/bednic/json-api/commit/ca6d01119edc8ae6744116c38901d4c130c9c9be))


### Fixed

* **Uri:** SortParser::parse incompatible behavior ([fad6240](https://gitlab.com/bednic/json-api/commit/fad6240842001bcb5d96bd200dd84229be57627b)), closes [#33](https://gitlab.com/bednic/json-api/issues/33)
* **Uri:** insufficient parsers behavior ([6657dea](https://gitlab.com/bednic/json-api/commit/6657deacd5479848246a5f0366a492bf054c66aa))
* **Uri:** LimitOffsetPagination const instead strings ([e51aa6d](https://gitlab.com/bednic/json-api/commit/e51aa6df21be4d4df15327f60f85ddcb8b7f4acc))
* **Uri:** PathParser ([423e4fb](https://gitlab.com/bednic/json-api/commit/423e4fbb207c749550e6f930848fd2def32be7df))
* **Uri:** SortParser behavior ([bc02bdc](https://gitlab.com/bednic/json-api/commit/bc02bdce445f26aee46d70e5455ae6b8bdbe9021))
* **Uri:** SortParser regex ([3ee4457](https://gitlab.com/bednic/json-api/commit/3ee44570e0ce0e7034b5694be3b8445332306e8a))
* **Uri:** SortParser::parse ([81cc097](https://gitlab.com/bednic/json-api/commit/81cc0978f814522f2234586be59e1f16e9e8d30b))
* **Uri:** PathParser::parse condition mistake ([fb394f7](https://gitlab.com/bednic/json-api/commit/fb394f791399b30445695e20a60dc4894359eed0))

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
