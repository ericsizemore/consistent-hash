# ConsistentHash

[![Build Status](https://scrutinizer-ci.com/g/ericsizemore/consistent-hash/badges/build.png?b=main)](https://scrutinizer-ci.com/g/ericsizemore/consistent-hash/build-status/main)
[![Code Coverage](https://scrutinizer-ci.com/g/ericsizemore/consistent-hash/badges/coverage.png?b=main)](https://scrutinizer-ci.com/g/ericsizemore/consistent-hash/?branch=main)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/ericsizemore/consistent-hash/badges/quality-score.png?b=main)](https://scrutinizer-ci.com/g/ericsizemore/consistent-hash/?branch=main)
[![Continuous Integration](https://github.com/ericsizemore/consistent-hash/actions/workflows/continuous-integration.yml/badge.svg)](https://github.com/ericsizemore/consistent-hash/actions/workflows/continuous-integration.yml)
[![Type Coverage](https://shepherd.dev/github/ericsizemore/consistent-hash/coverage.svg)](https://shepherd.dev/github/ericsizemore/consistent-hash)
[![Psalm Level](https://shepherd.dev/github/ericsizemore/consistent-hash/level.svg)](https://shepherd.dev/github/ericsizemore/consistent-hash)
[![Latest Stable Version](https://img.shields.io/packagist/v/esi/consistent-hash.svg)](https://packagist.org/packages/esi/consistent-hash)
[![Downloads per Month](https://img.shields.io/packagist/dm/esi/consistent-hash.svg)](https://packagist.org/packages/esi/consistent-hash)
[![License](https://img.shields.io/packagist/l/esi/consistent-hash.svg)](https://packagist.org/packages/esi/consistent-hash)

'Consistent Hash' is a small PHP library, which implements [consistent hashing](https://en.wikipedia.org/wiki/Consistent_hashing), which is most useful in distributed caching.

> [!NOTE]
> This library is a fork of https://github.com/pda/flexihash v3.0.0. The original library has not seen any releases since 2020.

## Installation

You can install the package via composer:

```bash
$ composer require esi/consistent-hash
```

## Usage

```php
use Esi\ConsistentHash;

$hash = new ConsistentHash();

// bulk add
$hash->addTargets(['cache-1', 'cache-2', 'cache-3']);

// simple lookup
$hash->lookup('object-a'); // "cache-1"
$hash->lookup('object-b'); // "cache-2"

// add and remove
$hash->addTarget('cache-4');
$hash->removeTarget('cache-1');

// lookup with next-best fallback (for redundant writes)
$hash->lookupList('object', 2); // ["cache-2", "cache-4"]

// remove cache-2, expect object to hash to cache-4
$hash->removeTarget('cache-2');
$hash->lookup('object'); // "cache-4"
```

## Benchmarks

#### PHPBench
Performance can be tested with [PHPBench](https://phpbench.readthedocs.io).

```bash
$ git clone https://github.com/ericsizemore/consistent-hash.git
$ cd consistent-hash
$ ./vendor/bin/phpbench run --report=aggregate --iterations=4 --tag=branch_main
```

Or via composer:

```bash
$ composer run-script benchmark
```

There is also an option to view results as a bar chart:

```bash
$ composer run-script benchmark:chart
```

With [opcache](https://www.php.net/manual/en/book.opcache.php) enabled:

```bash
# Normal, aggregate report
$ composer run-script benchmark:opcache

# Bar chart
$ composer run-script benchmark:chart:opcache
```

#### PHPUnit
Benchmarks are also available through PHPUnit, though it is a bit more rudimentary. To see these benchmarks, run:

```bash
$ composer run-script phpunit:benchmark
```

## About

### Requirements

* PHP >= 8.3

### Credits

- [Eric Sizemore](https://github.com/ericsizemore)
- [All Contributors](https://github.com/ericsizemore/consistent-hash/contributors)

And thanks to the library this is a fork of, [flexihash/flexihash](https://github.com/pda/flexihash):

- [Paul Annesley](https://github.com/pda)
- [Dom Morgan](https://github.com/dmnc)
- [All flexihash Contributors](https://github.com/pda/flexihash/graphs/contributors)


### Contributing

See [CONTRIBUTING](./CONTRIBUTING.md).

Bugs and feature requests are tracked on [GitHub](https://github.com/ericsizemore/consistent-hash/issues).

### Contributor Covenant Code of Conduct

See [CODE_OF_CONDUCT.md](./CODE_OF_CONDUCT.md)

### Backward Compatibility Promise

See [backward-compatibility.md](./backward-compatibility.md) for more information on Backwards Compatibility.

### Changelog

See the [CHANGELOG](./CHANGELOG.md) for more information on what has changed recently.

### License

See the [LICENSE](./LICENSE) for more information on the license that applies to this project.

### Roadmap/TODO

See the [ROADMAP](./ROADMAP.md) for more information what is currently being planned.

### Security

See [SECURITY](./SECURITY.md) for more information on the security disclosure process.

