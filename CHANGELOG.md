# Changelog

All notable changes to this project will be documented in this file.
The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

### [9.0.4](https://gitlab.com/bednic/json-api/compare/9.0.3...9.0.4) (2022-02-07)

### [9.0.3](https://gitlab.com/bednic/json-api/compare/9.0.2...9.0.3) (2022-02-07)


### Fixed

* URI parsing sequence ([e41a3fa](https://gitlab.com/bednic/json-api/commit/e41a3fac86d87c2cb87d3f05109d9ed366d348f5))

### [9.0.2](https://gitlab.com/bednic/json-api/compare/9.0.1...9.0.2) (2021-11-24)


### Fixed

* clear relationship and isRelationship properties in PathParser::parse() ([10dfe7d](https://gitlab.com/bednic/json-api/commit/10dfe7d8d2fafaacbfe2ef80ce4b860231940a7e)), closes [#88](https://gitlab.com/bednic/json-api/issues/88)

### [9.0.1](https://gitlab.com/bednic/json-api/compare/9.0.0...9.0.1) (2021-11-24)


### Fixed

* get size returns emtpy even it it is not empty ([02599b6](https://gitlab.com/bednic/json-api/commit/02599b6818aef55dd23cb1137cd36ec572249acd))
* remove usage of body::getSize ([3adb78d](https://gitlab.com/bednic/json-api/commit/3adb78d06f3016a10482ca0f3be530e6b3d4efd3))
* usage of content in middleware ([a785a9f](https://gitlab.com/bednic/json-api/commit/a785a9faeca254eb0a03315a74843f72c8f00d19))

## [9.0.0](https://gitlab.com/bednic/json-api/compare/8.1.0...9.0.0) (2021-11-10)


### ⚠ BREAKING CHANGES

* * Factory namespace removed
* Configuration class instead of parameters
* ParsedURI interface instead URIParser

### Added

* Add Builder instance and URIParser instance to ServerRequestInstance ([908b2a4](https://gitlab.com/bednic/json-api/commit/908b2a4bb24118a61dc1c524550e91661e3e1c58))
* Introduce Configuration ([d26b1df](https://gitlab.com/bednic/json-api/commit/d26b1df819b32a125fff2a79614684f4b772f9c6))


### Changed

* Enchant Conflict message ([5dfd81e](https://gitlab.com/bednic/json-api/commit/5dfd81e44ac4b3af6c33fbece5f5b35fc2dd3bed))
* remove final from DefaultErrorFactory ([d530347](https://gitlab.com/bednic/json-api/commit/d530347e285c36978527b12220e3c117226f1022))
* Replace assert with if ([e29cf74](https://gitlab.com/bednic/json-api/commit/e29cf74d7b789e8de7191e13cdae27e8114e2813))
* Type naming strategy changed to kebab case ([5b86806](https://gitlab.com/bednic/json-api/commit/5b86806d6699b96f9785841e90471bfecb4b5e9b)), closes [#40](https://gitlab.com/bednic/json-api/issues/40)


### Fixed

* bad assignment of attributes to request ([bcc02db](https://gitlab.com/bednic/json-api/commit/bcc02db8d3bdf43eda788e3d934fd1de6759d50f))
* Document Builder should accept null as parameter ([a5804a2](https://gitlab.com/bednic/json-api/commit/a5804a26ad1c89b283fe9902f89bc2b566969b74)), closes [#40](https://gitlab.com/bednic/json-api/issues/40)
* fix bad type of parameter ([41b008f](https://gitlab.com/bednic/json-api/commit/41b008f62d920c33e67e6ad224d3b57988487fed))
* check content-type header only if content is present ([00b0eb6](https://gitlab.com/bednic/json-api/commit/00b0eb6af1221f43185562a05a48d8cebdb9f6c7)), closes [#39](https://gitlab.com/bednic/json-api/issues/39)
* Possible issue when extending Metadata class ([15a6626](https://gitlab.com/bednic/json-api/commit/15a66269ea70f46ab0679f291b8f0ae36363733c))

## [8.1.0](https://gitlab.com/bednic/json-api/compare/8.0.2...8.1.0) (2021-05-27)


### Added

* Add support for nullable ([36d2062](https://gitlab.com/bednic/json-api/commit/36d20628470f05eeeebb5b4ffe41a47f09bbcf42))


### Changed

* Add UnexpectedFieldDataType exception ([d947ea7](https://gitlab.com/bednic/json-api/commit/d947ea790192f23cc5bd03325ce39b6af625093b))

### [8.0.2](https://gitlab.com/bednic/json-api/compare/8.0.1...8.0.2) (2021-05-24)


### Fixed

* **DocumentFactory:** reflection type name value ([47921c7](https://gitlab.com/bednic/json-api/commit/47921c79b16ca677510fe89c2947c2605c2bcf36))

### [8.0.1](https://gitlab.com/bednic/json-api/compare/8.0.0...8.0.1) (2021-05-24)


### Fixed

* **ExpressionFilterParser:** parseNull should return literal like other parse* methods ([532af6d](https://gitlab.com/bednic/json-api/commit/532af6db34fd0348b09d17c74dcb20aa4d46ebda))

## [8.0.0](https://gitlab.com/bednic/json-api/compare/7.4.4...8.0.0) (2021-04-13)


### ⚠ BREAKING CHANGES

* **ExpressionBuilder:** ExpressionBuilder methods ::toLower and ::toUpper
renamed to ::tolower, ::toupper

### Fixed

* **ExpressionBuilder:** tolower and toupper functions not working in filter parser ([9c0e4c5](https://gitlab.com/bednic/json-api/commit/9c0e4c5ae73406ada8e0fb5ea39c5ab3c856ca2b)), closes [#38](https://gitlab.com/bednic/json-api/issues/38)

### [7.4.4](https://gitlab.com/bednic/json-api/compare/7.4.3...7.4.4) (2021-04-12)

### Fixed

* included should be present even it it's empty, when called by
  uri ([16cf131](https://gitlab.com/bednic/json-api/commit/16cf1318a39079cbd028ad73b944b572e87f0893))

### [7.4.3](https://gitlab.com/bednic/json-api/compare/7.4.2...7.4.3) (2021-04-06)

### Fixed

* Fix not operand parsing ([e9597ab](https://gitlab.com/bednic/json-api/commit/e9597ab24eec6c5ea98679aebcd8cf96e0edd7de)), closes [#37](https://gitlab.com/bednic/json-api/issues/37)


### Changed

* Extract request body parsing to separate factory ([8c0cff5](https://gitlab.com/bednic/json-api/commit/8c0cff565915e318a4edb0be2193cf2e2a56aa7b))

### [7.4.2](https://gitlab.com/bednic/json-api/compare/7.4.1...7.4.2) (2021-03-31)


### Fixed

* **PsrJsonApiMiddleware:** fix issue when resource object does not contains attributes or relationships property ([f36e3b6](https://gitlab.com/bednic/json-api/commit/f36e3b6198fc17921ed7b6ba4d48d1e6cfc3714a))

### [7.4.1](https://gitlab.com/bednic/json-api/compare/7.4.0...7.4.1) (2021-03-31)


### Fixed

* **PsrJsonApiMiddleware:** replaces isset to property_exists ([91752cd](https://gitlab.com/bednic/json-api/commit/91752cd03e587eba2d88c7666ccabdca24afd510))

## [7.4.0](https://gitlab.com/bednic/json-api/compare/7.3.0...7.4.0) (2021-03-30)


### Added

* Data true types ([89f22d2](https://gitlab.com/bednic/json-api/commit/89f22d20ec153a94b4e181a3a9c280b97d712ee4))
* ResourceObject getAttributes, getRelationships ([72199b5](https://gitlab.com/bednic/json-api/commit/72199b50127f7a05db164141be5192283a2858cd))


### Fixed

* Remove trim in parseString ([4880cbf](https://gitlab.com/bednic/json-api/commit/4880cbf1b901d51a6b22b6b0381116d7261d80e6)), closes [#36](https://gitlab.com/bednic/json-api/issues/36)

## [7.3.0](https://gitlab.com/bednic/json-api/compare/7.2.0...7.3.0) (2021-03-23)


### Added

* added getPath and exists method to Paths class ([a101bf1](https://gitlab.com/bednic/json-api/commit/a101bf13580ce848b51c5d125a489a018f303c81))

## [7.2.0](https://gitlab.com/bednic/json-api/compare/7.1.2...7.2.0) (2021-03-20)


### Added

* new Encoder ([59bd8ac](https://gitlab.com/bednic/json-api/commit/59bd8ac67e50336a55b35ab448436196519a0d7a))

### [7.1.2](https://gitlab.com/bednic/json-api/compare/7.1.1...7.1.2) (2021-02-24)


### Fixed

* clear lexer before parsing expression ([ee2779f](https://gitlab.com/bednic/json-api/commit/ee2779f6020f851b5fd95844b45a40aa235a8c42))
* **OAS:** MediaType::schema as optional ([a70ab86](https://gitlab.com/bednic/json-api/commit/a70ab86634d50685d7c36f163ac985babf831ed4))

### [7.1.1](https://gitlab.com/bednic/json-api/compare/7.1.0...7.1.1) (2021-02-09)

### Fixed

* **OAS:** Bad error document response
  description ([63222cd](https://gitlab.com/bednic/json-api/commit/63222cd4d43adbb04036bfe7456b22c1efc8741b))
* Fix sort parsing ([e743c6d](https://gitlab.com/bednic/json-api/commit/e743c6d17b7c0d16ffd5f967b8b3756315806b99))

## [7.1.0](https://gitlab.com/bednic/json-api/compare/7.0.0...7.1.0) (2021-01-24)

### Fixed

* bad class instance in slice
  method ([133b0bc](https://gitlab.com/bednic/json-api/commit/133b0bc34969b8f4f3bfdcfa023869ca50a1639e))
* fix has implementation ([b99d823](https://gitlab.com/bednic/json-api/commit/b99d823c83b51ed316630dd13d1105d021fff63b))
* Change default filter expression
  builder ([05f7d63](https://gitlab.com/bednic/json-api/commit/05f7d63f9149eb6d68903cc1a9deaac5b21ba89e))
* Method isCollection bad
  behavior ([5323dbb](https://gitlab.com/bednic/json-api/commit/5323dbb48b5792c40f3575870b889643332f869e))
* Relationship::getData return
  type ([ec2729e](https://gitlab.com/bednic/json-api/commit/ec2729e3d717a6a9a2837f43f2168bd9d3f06b1a))
* wrong parameters passed to parent ctor in Relationship
  annotation ([d29095f](https://gitlab.com/bednic/json-api/commit/d29095f87e068a3e35abae6c009213b8d13b0aef))

### Added

* Add has filter
  function ([eb7d3d8](https://gitlab.com/bednic/json-api/commit/eb7d3d823968e6524b223ade287fc8965597f4dd))
* Add sort functions to
  Collection ([ba3a214](https://gitlab.com/bednic/json-api/commit/ba3a2140354efec439ac6a94f374328308360c70))

## [7.0.0](https://gitlab.com/bednic/json-api/compare/7.0.0-0...7.0.0) (2021-01-17)

## [7.0.0-0](https://gitlab.com/bednic/json-api/compare/6.4.0...7.0.0-0) (2020-12-14)

### ⚠ BREAKING CHANGES

* PHP8

### Changed

* **docs:** Move DocumentBuilder to
  Document\Builder ([38272a7](https://gitlab.com/bednic/json-api/commit/38272a7764a689feeabe919b2e8ba6a197603c25))
* **Document:** Move helper traits to Document
  namespace ([daaaf07](https://gitlab.com/bednic/json-api/commit/daaaf073988ed4299c146fa78e5557c2d0061eb4))
* **Exception:** Move OAS exceptions to Exception
  namespace ([b2f6162](https://gitlab.com/bednic/json-api/commit/b2f6162d26b25269f4960a272278f0ea9714d891))
* **Factory:** Wrap factories to separate namespace
  Factory ([56e6670](https://gitlab.com/bednic/json-api/commit/56e6670cc13106dbf2ca00af89ef6f5b30de7d6e))
* **OAS:** Rename OAS\Enum to
  OAS\Type ([589cd7d](https://gitlab.com/bednic/json-api/commit/589cd7d5506c7ea72e7f394d0e39e8d55a37b77f))
* Rename Uri namespace to
  URI ([0a8f6aa](https://gitlab.com/bednic/json-api/commit/0a8f6aa6064525fb6fefd6455ced31a90c537344))

### Fixed

* Revert removing NotFound
  exception ([74f134e](https://gitlab.com/bednic/json-api/commit/74f134e8ff1a4f8ec2959d1dbdee634cb9dd07f6))
* **Middleware:** Errors was not logged by
  logger ([cfd7e20](https://gitlab.com/bednic/json-api/commit/cfd7e200018d673833778907e9a9c77cc951f7d4))

### Added

* Migrate to PHP8 ([ddf9b76](https://gitlab.com/bednic/json-api/commit/ddf9b7670d446ed08a75a0542867682a5d85bf8d))
* **Collection:** Create own Collection
  class ([80fd582](https://gitlab.com/bednic/json-api/commit/80fd582b4fc570825d53ac635c9c3eb41175032c))
* **Helper:** Add
  DoctrineCollectionAdapter ([e5a114d](https://gitlab.com/bednic/json-api/commit/e5a114d8611ad2344de29ed177cc930487c8de02))

## [6.4.0](https://gitlab.com/bednic/json-api/compare/6.3.1...6.4.0) (2020-11-13)

### Added

* **Document:** Error
  factory ([ca6d011](https://gitlab.com/bednic/json-api/commit/ca6d01119edc8ae6744116c38901d4c130c9c9be))

### Fixed

* **Uri:** SortParser::parse incompatible
  behavior ([fad6240](https://gitlab.com/bednic/json-api/commit/fad6240842001bcb5d96bd200dd84229be57627b)),
  closes [#33](https://gitlab.com/bednic/json-api/issues/33)
* **Uri:** insufficient parsers
  behavior ([6657dea](https://gitlab.com/bednic/json-api/commit/6657deacd5479848246a5f0366a492bf054c66aa))
* **Uri:** LimitOffsetPagination const instead
  strings ([e51aa6d](https://gitlab.com/bednic/json-api/commit/e51aa6df21be4d4df15327f60f85ddcb8b7f4acc))
* **Uri:** PathParser ([423e4fb](https://gitlab.com/bednic/json-api/commit/423e4fbb207c749550e6f930848fd2def32be7df))
* **Uri:** SortParser
  behavior ([bc02bdc](https://gitlab.com/bednic/json-api/commit/bc02bdce445f26aee46d70e5455ae6b8bdbe9021))
* **Uri:** SortParser
  regex ([3ee4457](https://gitlab.com/bednic/json-api/commit/3ee44570e0ce0e7034b5694be3b8445332306e8a))
* **Uri:** SortParser::
  parse ([81cc097](https://gitlab.com/bednic/json-api/commit/81cc0978f814522f2234586be59e1f16e9e8d30b))
* **Uri:** PathParser::parse condition
  mistake ([fb394f7](https://gitlab.com/bednic/json-api/commit/fb394f791399b30445695e20a60dc4894359eed0))

### [6.3.1](https://gitlab.com/bednic/json-api/compare/6.3.0...6.3.1) (2020-10-26)

### Fixed

* Meta didnt accept very short prop
  names ([5c89997](https://gitlab.com/bednic/json-api/commit/5c899979c6d04e44af47b77d00b05f4e64e2f539))

## [6.3.0](https://gitlab.com/bednic/json-api/compare/6.2.3...6.3.0) (2020-10-26)

### Added

* ResourceCollection::
  toArray ([b8ef68c](https://gitlab.com/bednic/json-api/commit/b8ef68c52c2509a2a37524932eec2b3bcbe3f805))

### Changed

* Change constructor ([dd7119d](https://gitlab.com/bednic/json-api/commit/dd7119df06c76aa1246187ff19705eaf23f11a25))

### [6.2.3](https://gitlab.com/bednic/json-api/compare/6.2.2...6.2.3) (2020-10-25)

### Changed

* Resource::type is now not
  required ([2ab1b15](https://gitlab.com/bednic/json-api/commit/2ab1b1554f11155aefa9703df91736ac726e25a1))

### [6.2.2](https://gitlab.com/bednic/json-api/compare/6.2.1...6.2.2) (2020-10-24)

### Fixed

* **Middleware:** add body rewind after reading
  contents ([923aee8](https://gitlab.com/bednic/json-api/commit/923aee80ddbbbada1d24c94c8d510f291f6fb9ef))

### [6.2.1](https://gitlab.com/bednic/json-api/compare/6.2.0...6.2.1) (2020-10-23)

### Changed

* **OAS:** Add examples to OAS <parameters>
  schemas ([caaef1b](https://gitlab.com/bednic/json-api/commit/caaef1bba506ce0762b48ea79a510c98036614c0))

### Fixed

* **Middleware:** POST request throws false
  exception ([c582c48](https://gitlab.com/bednic/json-api/commit/c582c48ccc4a86cdda4a90f91519f4a50b48e98d))

## [6.2.0](https://gitlab.com/bednic/json-api/compare/6.1.0...6.2.0) (2020-10-15)

### Added

* Add ResourceObject::hasAttribute & ResourceObject::
  hasRelationship ([701ba6d](https://gitlab.com/bednic/json-api/commit/701ba6d9a9cd7f9d22fa4fe532d464146b123905)),
  closes [#29](https://gitlab.com/bednic/json-api/issues/29)
* add several function to ExpressionBuilder
  itnerface ([2903901](https://gitlab.com/bednic/json-api/commit/290390177081dcd604d89dd7ca6b3f7d2150120f))

### Fixed

* Fix potential bugs ([e3ed9fd](https://gitlab.com/bednic/json-api/commit/e3ed9fd0a488cddc0b1d1e8e127d3abd6422e29c))
* request body getSize may return
  null ([97212ac](https://gitlab.com/bednic/json-api/commit/97212ac2ba5b08c87ceafac8c3be12bab14c7a83)),
  closes [#30](https://gitlab.com/bednic/json-api/issues/30)

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

This release bringing significant refactor, so please pay attention to changes. Whole usage should be simpler. Most of
the responsibility can be now on DI. Another advantage of changes is, that you can have more then one metadata
factories, so you can have several API groups under one application.

### Added

* `ErrorSource` class which wraps Error::source, implements additional information to Error, like pointer, parameter or
  line of exception and trace
* `DocumentBuilderFactory` class which provides an easy setup of `DocumentBuilder`
  via `::new(ServerRequestInterface $request)` method
* `InclusionFetcher` class which extract inclusion fetch logic outside of `DocumentBuilder`

### Changed

* Relationships now mustn't contains key data
* All relationships returning collection of resources now must return `Collection` interface. Simple `array` was
  transferred internally anyway.
* Add InclusionInterface dependency to `Encoder::__construct`
* Renamed `UriPArtInterface` to `QueryPartInterface` to be more accurate
* `DocumentBuilder` was refactored to fit new use case
* Env `JSON_API_URL` is now at most cases `$baseURL` parameter in constructors, this should allow to have different
  instances of JSON API in one project
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
