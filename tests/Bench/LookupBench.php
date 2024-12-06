<?php

declare(strict_types=1);

/**
 * This file is part of Esi\ConsistentHash.
 *
 * (c) Eric Sizemore <admin@secondversion.com>
 * (c) Paul Annesley <paul@annesley.cc>
 *
 * This source file is subject to the MIT license. For the full copyright and
 * license information, please view the LICENSE file that was distributed with
 * this source code.
 */

namespace Esi\ConsistentHash\Tests\Bench;

use Esi\ConsistentHash\ConsistentHash;
use Esi\ConsistentHash\Hasher\HasherInterface;
use Esi\ConsistentHash\Hasher\Crc32Hasher;
use Esi\ConsistentHash\Hasher\Fnv1AHasher;
use Esi\ConsistentHash\Hasher\Md5Hasher;
use Esi\ConsistentHash\Hasher\Murmur3Hasher;
use Esi\ConsistentHash\Hasher\Xxh32Hasher;
use Generator;
use PhpBench\Attributes\ParamProviders;

use function bin2hex;
use function random_bytes;
use function range;

final class LookupBench
{
    private ConsistentHash $hasher;

    /** @var list<string> */
    private array $randomKeys = [];

    public function __construct()
    {
        // Generate random lookup keys outside the measurement function.
        foreach (range(1, 100_000) as $ignored) {
            $this->randomKeys[] = bin2hex(random_bytes(12));
        }
    }

    /**
     * @param array{algo: class-string<HasherInterface>, count: int} $params
     */
    #[ParamProviders(['provideLookupCount', 'provideHashers'])]
    public function benchLookup(array $params): void
    {
        $this->hasher = new ConsistentHash(new $params['algo'](), 64);

        foreach (range(1, 10) as $i) {
            $this->hasher->addTarget('target_' . $i, 1);
        }

        foreach (range(0, $params['count'] - 1) as $i) {
            $this->hasher->lookup($this->randomKeys[$i]);
        }
    }

    public function provideHashers(): Generator
    {
        yield 'CRC32' => ['algo' => Crc32Hasher::class];
        yield 'FNV1A' => ['algo' => Fnv1AHasher::class];
        yield 'MD5' => ['algo' => Md5Hasher::class];
        yield 'MURMUR3' => ['algo' => Murmur3Hasher::class];
        yield 'XXH32' => ['algo' => Xxh32Hasher::class];
    }

    public function provideLookupCount(): Generator
    {
        yield '10_000 lookups' => ['count' => 10_000];
        yield '20_000 lookups' => ['count' => 20_000];
        yield '40_000 lookups' => ['count' => 40_000];
        yield '100_000 lookups' => ['count' => 100_000];
    }
}
