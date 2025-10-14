# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

 
## [2.0.0] - 2025-10-14

### Added

  * Added `RectorPHP` to dev-dependencies.
    * Rector config found at `rector.php`.
  * Added new Exception `Esi\ConsistentHash\Exception\InvalidArgumentException`.

### Changed

  * Minimum PHP version bumped to PHP 8.3.
  * Updated `composer.json` for `RectorPHP`.
  * Updated `.gitattributes` to add more files to `export-ignore`.
  * Updated `CONTRIBUTING` information.
  * Small updates to project `README`.
  * Updated `SECURITY` to be more thorough.
  * Small code updates throughout via `PHP-CS-Fixer` and `Rector`.
  * Increased code coverage to 100%.
  * [BC-BREAK] `ConsistentHash::__construct()` no longer accepts null arguments:
    * With the use of constructor property promotion, null parameters are no longer needed.
    * If called without parameters, the hasher will default to `CRC32` and `replicas` will default to 64; same as in `v1.x`.
  * [BC-BREAK] `ConsistentHash::__construct()` will now throw an `Esi\ConsistentHash\Exception\InvalidArgumentException` if `$replicas` is not greater than `0`.
  * [BC-BREAK] `ConsistentHash::addTarget()` will now throw an `Esi\ConsistentHash\Exception\InvalidArgumentException` if `$weight` is less than `0.0`.
  * [BC-BREAK] All classes now marked `final`.


## [1.1.0] - 2024-12-10

### Added

  * Added [Fnv1-a](https://en.wikipedia.org/wiki/Fowler%E2%80%93Noll%E2%80%93Vo_hash_function) and [Murmur3](https://github.com/aappleby/smhasher) based hashers. This brings the available hashers to:
      * `Crc32Hasher`
      * `Fnv1AHasher`
      * `Md5Hasher`
      * `Murmur3Hasher`
      * `Xxh32Hasher`
  * Added unit tests for the `Fnv1-a` and `Murmur3` based hashers.
  * Added `tests/BenchmarkTest` back, which was previously removed in the 1.0.0 release.

### Changed

  * Improved the PHPBench benchmark in `tests/Bench` and reduced to one class.

### Removed

  * With the improvement to `tests/Bench/LookupBench`, the following were no longer needed:
    * `tests/Bench/LookupBenchCrc32`
    * `tests/Bench/LookupBenchFnv1A`
    * `tests/Bench/LookupBenchMurmur3`
    * `tests/Bench/LookupBenchXxh32`


## [1.0.0] - 2024-12-04

This library is based on a fork of [flexihash/flexihash](https://github.com/pda/flexihash) v3.0.0 by:
 * Paul Annesley <paul@annesley.cc>
 * Dom Morgan <dom@d3r.com> 

**Note:** This is not a drop-in replacement for `flexihash` as there are various BC breaks.

### Added

  * Added a [XXH32](https://xxhash.com/) based hasher. This brings the available hashers to:
    * `Crc32Hasher`
    * `Md5Hasher`
    * `Xxh32Hasher`
  * Added benchmarks for the `Crc32` and `Xxh32` based hashers, as the original benchmark was using the MD5 hasher. Adds: 
    * `tests/Bench/LookupBenchCrc32`
    * `tests/Bench/LookupBenchXxh32`
  * New dev dependencies:
    * `esi/phpunit-coverage-check` - to check code coverage percentage based on PHPUnits' clover output.
    * `friendsofphp/php-cs-fixer`  - to check and/or fix code based on my personal coding standards.
    * `phpbench/phpbench` - to run benchmarks found in `tests/Bench`.
    * PHPStan for static analysis:
      * `phpstan/extension-installer`
      * `phpstan/phpstan`
      * `phpstan/phpstan-deprecation-rules`
      * `phpstan/phpstan-phpunit`
      * `phpstan/phpstan-strict-rules`
    * `phpunit/phpunit` - to perform unit tests.
    * Psalm for static analysis:
      * `psalm/plugin-phpunit`
      * `vimeo/psalm`

### Changed

  * Updated namespace to `Esi\ConsistentHash`.
  * Bumped minimum PHP version to 8.2.
  * Updated throughout to add proper parameter, property, and return types.
  * Updated throughout per coding standards via PHP-CS-Fixer (PSR-12/PER-CS).
  * Updated throughout to add psalm and phpstan extended types to docblocks.
  * Various fixes throughout to resolve issues reported by both PHPStan and Psalm.
  * Updated `composer.json` to add keywords, support information, and scripts.
  * Normalized `composer.json`.
  * Performance enhancements.
  * Changed `Exception` class to `TargetException` and moved to `Esi\ConsistentHash\Exception` namespace.
    * `TargetException` now extends `\RuntimeException` instead of `\Exception`.
    * Added static methods that handle creating the exception and message.

### Removed

  * Removed TravisCI and Coveralls.
  * Removed tests/BenchmarkTest in favor of just using a slimmed down benchmark via PHPBench.


[unreleased]: https://github.com/ericsizemore/consistent-hash/tree/main
[2.0.0]: https://github.com/ericsizemore/consistent-hash/releases/tag/2.0.0
[1.1.0]: https://github.com/ericsizemore/consistent-hash/releases/tag/1.1.0
[1.0.0]: https://github.com/ericsizemore/consistent-hash/releases/tag/1.0.0
