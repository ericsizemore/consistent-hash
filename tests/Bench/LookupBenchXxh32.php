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
use Esi\ConsistentHash\Hasher\Xxh32Hasher;
use Generator;
use PhpBench\Attributes\ParamProviders;

use function bin2hex;
use function random_bytes;
use function range;

final class LookupBenchXxh32
{
    private ConsistentHash $hasher;

    /** @var list<string> */
    private array $randomKeys = [];

    public function __construct()
    {
        $this->hasher = new ConsistentHash(new Xxh32Hasher(), 64);

        foreach (range(1, 10) as $i) {
            $this->hasher->addTarget('target_' . $i, 1);
        }

        // Generate random lookup keys outside the measurement function.
        foreach (range(1, 100_000) as $ignored) {
            $this->randomKeys[] = bin2hex(random_bytes(12));
        }
    }

    /**
     * @param array{count: int} $params
     */
    #[ParamProviders(['provideLookupCount', 'provideHasher'])]
    public function benchLookup(array $params): void
    {
        foreach (range(0, $params['count'] - 1) as $i) {
            $this->hasher->lookup($this->randomKeys[$i]);
        }
    }

    public function provideHasher(): Generator
    {
        yield 'XXH32' => ['algo' => 'XXH32'];
    }

    public function provideLookupCount(): Generator
    {
        yield '10_000 lookups' => ['count' => 10_000];
        yield '20_000 lookups' => ['count' => 20_000];
        yield '40_000 lookups' => ['count' => 40_000];
        yield '100_000 lookups' => ['count' => 100_000];
    }
}
