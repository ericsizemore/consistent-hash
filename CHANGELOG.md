# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).


## [Unreleased]

### Added

  * Added a [Murmur3](https://github.com/aappleby/smhasher) based hasher. This brings the available hashers to:
      * `Crc32Hasher`
      * `Md5Hasher`
      * `Murmur3Hasher`
      * `Xxh32Hasher`
  * Added a benchmark, and unit test, for the `Murmur3` based hasher. Adds:
    * `tests/Bench/LookupBenchMurmur3`


## [1.0.0] - 2024-12-04

This library is based on a fork of [flexihash/flexihash](https://github.com/pda/flexihash) v3.0.0 by
 * Paul Annesley <paul@annesley.cc>
 * Dom Morgan <dom@d3r.com> 

**Note:** This is not a drop-in replacement for flexihash as there are various BC breaks.

### Added

  * Added a [XXH32](https://xxhash.com/) based hasher. This brings the available hashers to:
    * `Crc32Hasher`
    * `Md5Hasher`
    * `Xxh32Hasher`
  * Added benchmarks for the `Crc32` and `Xxh32` based hashers, as the original benchmark was using the MD5 hasher. Adds: 
    * `tests/Bench/LookupBenchCrc32`
    * `tests/Bench/LookupBenchXxh32`
  * New dev dependencies:
    * `esi/phpunit-coverage-check` - to check code coverage percentage based on PHPUnit's clover output
    * `friendsofphp/php-cs-fixer`  - to check and/or fix code based on my personal coding standards
    * `phpbench/phpbench` - to run benchmarks found in `tests/Bench`
    * PHPStan for static analysis
      * `phpstan/extension-installer`
      * `phpstan/phpstan`
      * `phpstan/phpstan-deprecation-rules`
      * `phpstan/phpstan-phpunit`
      * `phpstan/phpstan-strict-rules`
    * `phpunit/phpunit` - to perform unit tests
    * Psalm for static analysis
      * `psalm/plugin-phpunit`
      * `vimeo/psalm`

### Changed

  * Updated namespace to `Esi\ConsistentHash`
  * Bumped minimum PHP version to 8.2
  * Updated throughout to add proper parameter, property, and return types
  * Updated throughout per coding standards via PHP-CS-Fixer (PSR-12/PER-CS)
  * Updated throughout to add psalm and phpstan extended types to docblocks
  * Various fixes throughout to resolve issues reported by both PHPStan and Psalm
  * Updated composer.json to add keywords, support information, and scripts
  * Normalized composer.json
  * Performance enhancements
  * Changed `Exception` class to `TargetException` and moved to `Esi\ConsistentHash\Exception` namespace
    * `TargetException` now extends `\RuntimeException` instead of `\Exception`
    * Added static methods that handle creating the exception and message

### Removed

  * Removed TravisCI and Coveralls
  * Removed tests/BenchmarkTest in favor of just using a slimmed down benchmark via PHPBench


[unreleased]: https://github.com/ericsizemore/consistent-hash/tree/main
[1.0.0]: https://github.com/ericsizemore/consistent-hash/releases/tag/1.0.0
