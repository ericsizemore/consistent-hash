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

/**
 * @internal
 */
#[CoversClass(Crc32Hasher::class)]
#[CoversClass(Fnv1AHasher::class)]
#[CoversClass(Md5Hasher::class)]
#[CoversClass(Murmur3Hasher::class)]
#[CoversClass(Xxh32Hasher::class)]
#[UsesClass(ConsistentHash::class)]
class HasherTest extends TestCase
{
    public function testCrc32Hash(): void
    {
        $hasher  = new Crc32Hasher();
        $result1 = $hasher->hash('test');
        $result2 = $hasher->hash('test');
        $result3 = $hasher->hash('different');

        self::assertEquals($result1, $result2);
        self::assertNotEquals($result1, $result3); // fragile but worthwhile
    }

    public function testCrc32HashSpaceLookupsAreValidTargets(): void
    {
        $targets = [];
        foreach (range(1, 10) as $i) {
            $targets[] = \sprintf('target%s', $i);
        }

        $hashSpace = new ConsistentHash(new Crc32Hasher());
        $hashSpace->addTargets($targets);

        foreach (range(1, 10) as $i) {
            self::assertTrue(
                \in_array($hashSpace->lookup(\sprintf('r%s', $i)), $targets, true),
                'target must be in list of targets',
            );
        }
    }

    public function testFnv1AHash(): void
    {
        $hasher  = new Fnv1AHasher();
        $result1 = $hasher->hash('test');
        $result2 = $hasher->hash('test');
        $result3 = $hasher->hash('different');

        self::assertEquals($result1, $result2);
        self::assertNotEquals($result1, $result3); // fragile but worthwhile
    }

    public function testFnv1AHashSpaceLookupsAreValidTargets(): void
    {
        $targets = [];
        foreach (range(1, 10) as $i) {
            $targets[] = \sprintf('target%s', $i);
        }

        $hashSpace = new ConsistentHash(new Fnv1AHasher());
        $hashSpace->addTargets($targets);

        foreach (range(1, 10) as $i) {
            self::assertTrue(
                \in_array($hashSpace->lookup(\sprintf('r%s', $i)), $targets, true),
                'target must be in list of targets',
            );
        }
    }

    public function testMd5Hash(): void
    {
        $hasher  = new Md5Hasher();
        $result1 = $hasher->hash('test');
        $result2 = $hasher->hash('test');
        $result3 = $hasher->hash('different');

        self::assertEquals($result1, $result2);
        self::assertNotEquals($result1, $result3); // fragile but worthwhile
    }

    public function testMd5HashSpaceLookupsAreValidTargets(): void
    {
        $targets = [];
        foreach (range(1, 10) as $i) {
            $targets[] = \sprintf('target%s', $i);
        }

        $hashSpace = new ConsistentHash(new Md5Hasher());
        $hashSpace->addTargets($targets);

        foreach (range(1, 10) as $i) {
            self::assertTrue(
                \in_array($hashSpace->lookup(\sprintf('r%s', $i)), $targets, true),
                'target must be in list of targets',
            );
        }
    }

    public function testMurmur3Hash(): void
    {
        $hasher  = new Murmur3Hasher();
        $result1 = $hasher->hash('test');
        $result2 = $hasher->hash('test');
        $result3 = $hasher->hash('different');

        self::assertEquals($result1, $result2);
        self::assertNotEquals($result1, $result3); // fragile but worthwhile
    }

    public function testMurmur3HashSpaceLookupsAreValidTargets(): void
    {
        $targets = [];
        foreach (range(1, 10) as $i) {
            $targets[] = \sprintf('target%s', $i);
        }

        $hashSpace = new ConsistentHash(new Murmur3Hasher());
        $hashSpace->addTargets($targets);

        foreach (range(1, 10) as $i) {
            self::assertTrue(
                \in_array($hashSpace->lookup(\sprintf('r%s', $i)), $targets, true),
                'target must be in list of targets',
            );
        }
    }

    public function testXxh32Hash(): void
    {
        $hasher  = new Xxh32Hasher();
        $result1 = $hasher->hash('test');
        $result2 = $hasher->hash('test');
        $result3 = $hasher->hash('different');

        self::assertEquals($result1, $result2);
        self::assertNotEquals($result1, $result3); // fragile but worthwhile
    }

    public function testXxh32HashSpaceLookupsAreValidTargets(): void
    {
        $targets = [];
        foreach (range(1, 10) as $i) {
            $targets[] = \sprintf('target%s', $i);
        }

        $hashSpace = new ConsistentHash(new Xxh32Hasher());
        $hashSpace->addTargets($targets);

        foreach (range(1, 10) as $i) {
            self::assertTrue(
                \in_array($hashSpace->lookup(\sprintf('r%s', $i)), $targets, true),
                'target must be in list of targets',
            );
        }
    }
}
