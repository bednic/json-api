# Changelog

All notable changes to this project will be documented in this file.
The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).


## [Unreleased]

### Added
* `ErrorSource` class which wraps Error::source, implements additional information to Error,
like pointer, parameter or line of exception and trace
* `Config` static class
    * `ENDPOINT` for setup API endpoint url
    * `MAX_INCLUDED_ITEMS` sets max items included in a document, to prevent huge files
    * `RELATIONSHIP_LIMIT` sets limit of items in `relationship/data`
    * `RELATIONSHIP_DATA` toggle if relationships should or shouldn't contain data at all
    * `INCLUSION_SUPPORT` enable *include* url parameter support
    * `SORT_SUPPORT` enable *sort* url parameter support
    * `PAGINATION_SUPPORT` enable *page* url parameter support

### Changed
* Relationships now mustn't contains key data
* `DocumentBuilder::create` now server as the simplest possible use-case for using DocumentBuilder
* `DocumentBuilder::__constructor` is now public and replace old `::create` function
* All relationships returning collection of resources now must return `Collection` interface.
Simple `array` was transferred internally anyway.

### Deprecated

### Fixed

### Removed
* `JsonSerializable`
* `JsonConvertible`
* `API_ENV_URL`

---
[Unreleased]: https://gitlab.com/bednic/json-api/compare/5.1.7...6.x