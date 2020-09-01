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
[Unreleased]: https://gitlab.com/bednic/json-api/compare/6.0.0...6.x
[6.0.0]: https://gitlab.com/bednic/json-api/compare/5.1.7...6.0.0
