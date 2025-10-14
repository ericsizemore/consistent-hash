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

namespace Esi\ConsistentHash\Tests\Hasher;

use Esi\ConsistentHash\ConsistentHash;
use Esi\ConsistentHash\Hasher\Crc32Hasher;
use Esi\ConsistentHash\Hasher\Fnv1AHasher;
use Esi\ConsistentHash\Hasher\Md5Hasher;
use Esi\ConsistentHash\Hasher\Murmur3Hasher;
use Esi\ConsistentHash\Hasher\Xxh32Hasher;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

use function range;

/**
 * @internal
 */
#[CoversClass(Crc32Hasher::class)]
#[CoversClass(Fnv1AHasher::class)]
#[CoversClass(Md5Hasher::class)]
#[CoversClass(Murmur3Hasher::class)]
#[CoversClass(Xxh32Hasher::class)]
#[UsesClass(ConsistentHash::class)]
final class HasherTest extends TestCase
{
    public function testCrc32Hash(): void
    {
        $crc32Hasher = new Crc32Hasher();
        $result1     = $crc32Hasher->hash('test');
        $result2     = $crc32Hasher->hash('test');
        $result3     = $crc32Hasher->hash('different');

        self::assertSame($result1, $result2);
        self::assertNotSame($result1, $result3); // fragile but worthwhile
    }

    public function testCrc32HashSpaceLookupsAreValidTargets(): void
    {
        $targets = [];

        foreach (range(1, 10) as $i) {
            $targets[] = \sprintf('target%s', $i);
        }

        $consistentHash = new ConsistentHash(new Crc32Hasher());
        $consistentHash->addTargets($targets);

        foreach (range(1, 10) as $i) {
            self::assertContains(
                $consistentHash->lookup(\sprintf('r%s', $i)),
                $targets,
                'target must be in list of targets',
            );
        }
    }

    public function testFnv1AHash(): void
    {
        $fnv1AHasher = new Fnv1AHasher();
        $result1     = $fnv1AHasher->hash('test');
        $result2     = $fnv1AHasher->hash('test');
        $result3     = $fnv1AHasher->hash('different');

        self::assertSame($result1, $result2);
        self::assertNotSame($result1, $result3); // fragile but worthwhile
    }

    public function testFnv1AHashSpaceLookupsAreValidTargets(): void
    {
        $targets = [];

        foreach (range(1, 10) as $i) {
            $targets[] = \sprintf('target%s', $i);
        }

        $consistentHash = new ConsistentHash(new Fnv1AHasher());
        $consistentHash->addTargets($targets);

        foreach (range(1, 10) as $i) {
            self::assertContains(
                $consistentHash->lookup(\sprintf('r%s', $i)),
                $targets,
                'target must be in list of targets',
            );
        }
    }

    public function testMd5Hash(): void
    {
        $md5Hasher = new Md5Hasher();
        $result1   = $md5Hasher->hash('test');
        $result2   = $md5Hasher->hash('test');
        $result3   = $md5Hasher->hash('different');

        self::assertSame($result1, $result2);
        self::assertNotSame($result1, $result3); // fragile but worthwhile
    }

    public function testMd5HashSpaceLookupsAreValidTargets(): void
    {
        $targets = [];

        foreach (range(1, 10) as $i) {
            $targets[] = \sprintf('target%s', $i);
        }

        $consistentHash = new ConsistentHash(new Md5Hasher());
        $consistentHash->addTargets($targets);

        foreach (range(1, 10) as $i) {
            self::assertContains(
                $consistentHash->lookup(\sprintf('r%s', $i)),
                $targets,
                'target must be in list of targets',
            );
        }
    }

    public function testMurmur3Hash(): void
    {
        $murmur3Hasher = new Murmur3Hasher();
        $result1       = $murmur3Hasher->hash('test');
        $result2       = $murmur3Hasher->hash('test');
        $result3       = $murmur3Hasher->hash('different');

        self::assertSame($result1, $result2);
        self::assertNotSame($result1, $result3); // fragile but worthwhile
    }

    public function testMurmur3HashSpaceLookupsAreValidTargets(): void
    {
        $targets = [];

        foreach (range(1, 10) as $i) {
            $targets[] = \sprintf('target%s', $i);
        }

        $consistentHash = new ConsistentHash(new Murmur3Hasher());
        $consistentHash->addTargets($targets);

        foreach (range(1, 10) as $i) {
            self::assertContains(
                $consistentHash->lookup(\sprintf('r%s', $i)),
                $targets,
                'target must be in list of targets',
            );
        }
    }

    public function testXxh32Hash(): void
    {
        $xxh32Hasher = new Xxh32Hasher();
        $result1     = $xxh32Hasher->hash('test');
        $result2     = $xxh32Hasher->hash('test');
        $result3     = $xxh32Hasher->hash('different');

        self::assertSame($result1, $result2);
        self::assertNotSame($result1, $result3); // fragile but worthwhile
    }

    public function testXxh32HashSpaceLookupsAreValidTargets(): void
    {
        $targets = [];

        foreach (range(1, 10) as $i) {
            $targets[] = \sprintf('target%s', $i);
        }

        $consistentHash = new ConsistentHash(new Xxh32Hasher());
        $consistentHash->addTargets($targets);

        foreach (range(1, 10) as $i) {
            self::assertContains(
                $consistentHash->lookup(\sprintf('r%s', $i)),
                $targets,
                'target must be in list of targets',
            );
        }
    }
}
