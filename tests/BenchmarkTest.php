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

namespace Esi\ConsistentHash\Tests;

use Esi\ConsistentHash\ConsistentHash;
use Esi\ConsistentHash\Hasher\Crc32Hasher;
use Esi\ConsistentHash\Hasher\Fnv1AHasher;
use Esi\ConsistentHash\Hasher\Md5Hasher;
use Esi\ConsistentHash\Hasher\Murmur3Hasher;
use Esi\ConsistentHash\Hasher\Xxh32Hasher;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

use function abs;
use function array_keys;
use function array_sum;
use function array_values;
use function crc32;
use function floor;
use function max;
use function microtime;
use function min;
use function range;
use function round;
use function sort;

/**
 * Benchmarks, not really tests.
 *
 * @internal
 */
#[CoversClass(ConsistentHash::class)]
#[CoversClass(Crc32Hasher::class)]
#[CoversClass(Fnv1AHasher::class)]
#[CoversClass(Md5Hasher::class)]
#[CoversClass(Murmur3Hasher::class)]
#[CoversClass(Xxh32Hasher::class)]
#[Group('benchmark')]
#[DoesNotPerformAssertions]
final class BenchmarkTest extends TestCase
{
    private int $lookups = 1000;

    private int $targets = 10;

    public function dump(string $message): void
    {
        echo \sprintf("%s\n\n", $message);
    }

    public function testAddTargetWithNonConsistentHash(): void
    {
        $results1 = [];

        foreach (range(1, $this->lookups) as $i) {
            $results1[$i] = $this->basicHash('t' . $i, 10);
        }

        $results2 = [];

        foreach (range(1, $this->lookups) as $i) {
            $results2[$i] = $this->basicHash('t' . $i, 11);
        }

        $differences = 0;

        foreach (range(1, $this->lookups) as $i) {
            if ($results1[$i] !== $results2[$i]) {
                ++$differences;
            }
        }

        $percent = round((int) ($differences / $this->lookups) * 100);

        $this->dump(\sprintf(
            'NonConsistentHash: %.2f%% of lookups changed after adding a target to the existing %d',
            $percent,
            $this->targets
        ));
    }

    public function testHashDistributionWithCrc32Hasher(): void
    {
        $consistentHash = new ConsistentHash(
            new Crc32Hasher()
        );

        foreach (range(1, $this->targets) as $i) {
            $consistentHash->addTarget('target' . $i);
        }

        $results = [];

        foreach (range(1, $this->lookups) as $i) {
            $results[$i] = $consistentHash->lookup('t' . $i);
        }

        $distribution = [];

        foreach ($consistentHash->getAllTargets() as $allTarget) {
            $distribution[$allTarget] = \count(array_keys($results, $allTarget, true));
        }

        \assert($distribution !== []);

        $this->dump(\sprintf(
            'Distribution of %d lookups per target (min/max/median/avg): %d/%d/%.0f/%.0f',
            $this->lookups / $this->targets,
            min($distribution),
            max($distribution),
            round($this->median($distribution)),
            round(array_sum($distribution) / \count($distribution))
        ));
    }

    public function testHasherSpeed(): void
    {
        $hashCount = 100_000;

        $md5Hasher     = new Md5Hasher();
        $crc32Hasher   = new Crc32Hasher();
        $fnv1AHasher   = new Fnv1AHasher();
        $murmur3Hasher = new Murmur3Hasher();
        $xxh32Hasher   = new Xxh32Hasher();

        $start = microtime(true);

        for ($i = 0; $i < $hashCount; ++$i) {
            $md5Hasher->hash('test' . $i);
        }

        $timeMd5 = microtime(true) - $start;
        $start   = microtime(true);

        for ($i = 0; $i < $hashCount; ++$i) {
            $crc32Hasher->hash('test' . $i);
        }

        $timeCrc32 = microtime(true) - $start;
        $start     = microtime(true);

        for ($i = 0; $i < $hashCount; ++$i) {
            $fnv1AHasher->hash('test' . $i);
        }

        $timeFnv1 = microtime(true) - $start;
        $start    = microtime(true);

        for ($i = 0; $i < $hashCount; ++$i) {
            $murmur3Hasher->hash('test' . $i);
        }

        $timeMurmur3 = microtime(true) - $start;
        $start       = microtime(true);

        for ($i = 0; $i < $hashCount; ++$i) {
            $xxh32Hasher->hash('test' . $i);
        }

        $timeXxh32 = microtime(true) - $start;

        $this->dump(\sprintf(
            "Hashers timed over %d hashes (MD5 / CRC32 / FNV1-A / MURMUR3 / XXH32):\n %f / %f / %f / %f / %f",
            $hashCount,
            $timeMd5,
            $timeCrc32,
            $timeFnv1,
            $timeMurmur3,
            $timeXxh32
        ));
    }

    public function testHopeAddingTargetDoesNotChangeMuchWithCrc32Hasher(): void
    {
        $consistentHash = new ConsistentHash(
            new Crc32Hasher()
        );

        foreach (range(1, $this->targets) as $i) {
            $consistentHash->addTarget('target' . $i);
        }

        $results1 = [];

        foreach (range(1, $this->lookups) as $i) {
            $results1[$i] = $consistentHash->lookup('t' . $i);
        }

        $consistentHash->addTarget('target-new');

        $results2 = [];

        foreach (range(1, $this->lookups) as $i) {
            $results2[$i] = $consistentHash->lookup('t' . $i);
        }

        $differences = 0;

        foreach (range(1, $this->lookups) as $i) {
            if ($results1[$i] !== $results2[$i]) {
                ++$differences;
            }
        }

        $percent = round((int) ($differences / $this->lookups) * 100);

        $this->dump(
            \sprintf(
                'ConsistentHash: %.2f%% of lookups changed after adding a target to the existing %d',
                $percent,
                $this->targets
            )
        );
    }

    public function testHopeRemovingTargetDoesNotChangeMuchWithCrc32Hasher(): void
    {
        $consistentHash = new ConsistentHash(
            new Crc32Hasher()
        );

        foreach (range(1, $this->targets) as $i) {
            $consistentHash->addTarget('target' . $i);
        }

        $results1 = [];

        foreach (range(1, $this->lookups) as $i) {
            $results1[$i] = $consistentHash->lookup('t' . $i);
        }

        $consistentHash->removeTarget('target1');

        $results2 = [];

        foreach (range(1, $this->lookups) as $i) {
            $results2[$i] = $consistentHash->lookup('t' . $i);
        }

        $differences = 0;

        foreach (range(1, $this->lookups) as $i) {
            if ($results1[$i] !== $results2[$i]) {
                ++$differences;
            }
        }

        $percent = round((int) ($differences / $this->lookups) * 100);

        $this->dump(
            \sprintf(
                'ConsistentHash: %.2f%% of lookups changed  after removing 1 of %d targets',
                $percent,
                $this->targets
            )
        );
    }

    public function testRemoveTargetWithNonConsistentHash(): void
    {
        $results1 = [];

        foreach (range(1, $this->lookups) as $i) {
            $results1[$i] = $this->basicHash('t' . $i, 10);
        }

        $results2 = [];

        foreach (range(1, $this->lookups) as $i) {
            $results2[$i] = $this->basicHash('t' . $i, 9);
        }

        $differences = 0;

        foreach (range(1, $this->lookups) as $i) {
            if ($results1[$i] !== $results2[$i]) {
                ++$differences;
            }
        }

        $percent = round((int) ($differences / $this->lookups) * 100);

        $this->dump(
            \sprintf(
                'NonConsistentHash: %.2f%% of lookups changed after removing 1 of %d targets',
                $percent,
                $this->targets
            )
        );
    }

    // ----------------------------------------

    private function basicHash(string $value, int $targets): int
    {
        return abs(crc32($value) % $targets);
    }

    /**
     * @param array<int> $values list of numeric values
     */
    private function median(array $values): float|int
    {
        $values = array_values($values);
        sort($values);

        $count       = \count($values);
        $middleFloor = (int) floor($count / 2);

        if ($count % 2 === 1) {
            return $values[$middleFloor];
        }

        return ($values[$middleFloor] + $values[$middleFloor + 1]) / 2;
    }
}
