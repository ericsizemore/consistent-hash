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
use Esi\ConsistentHash\Exception\InvalidArgumentException;
use Esi\ConsistentHash\Exception\TargetException;
use Esi\ConsistentHash\Hasher\Crc32Hasher;
use Esi\ConsistentHash\Tests\Hasher\MockHasher;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

use function range;

/**
 * @internal
 */
#[CoversClass(ConsistentHash::class)]
#[CoversClass(TargetException::class)]
#[CoversClass(InvalidArgumentException::class)]
#[UsesClass(Crc32Hasher::class)]
#[Group('default')]
final class ConsistentHashTest extends TestCase
{
    public function testAddTargetAndGetAllTargets(): void
    {
        $consistentHash = new ConsistentHash();
        $consistentHash->addTarget('t-a');
        $consistentHash->addTarget('t-b');
        $consistentHash->addTarget('t-c');

        self::assertSame(['t-a', 't-b', 't-c'], $consistentHash->getAllTargets());
    }

    public function testAddTargetsAndGetAllTargets(): void
    {
        $targets = ['t-a', 't-b', 't-c'];

        $consistentHash = new ConsistentHash();
        $consistentHash->addTargets($targets);
        self::assertSame($consistentHash->getAllTargets(), $targets);
    }

    public function testAddTargetThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $consistentHash = new ConsistentHash();
        $consistentHash->addTarget('t-a');
        $consistentHash->addTarget('t-b', -1.0);
        $consistentHash->addTarget('t-c');
    }

    public function testAddTargetThrowsExceptionOnDuplicateTarget(): void
    {
        $consistentHash = new ConsistentHash();
        $consistentHash->addTarget('t-a');
        $this->expectException(TargetException::class);
        $consistentHash->addTarget('t-a');
    }

    public function testBisectLeftWithValueLessThanLowest(): void
    {
        $mockHasher     = new MockHasher(0);
        $consistentHash = new ConsistentHash($mockHasher, 1);

        // Add targets with hash values 10, 20, 30
        $mockHasher->setHashValue(10);
        $consistentHash->addTarget('t1');

        $mockHasher->setHashValue(20);
        $consistentHash->addTarget('t2');

        $mockHasher->setHashValue(30);
        $consistentHash->addTarget('t3');

        // Now lookup with a hash value (5) that's less than the lowest position (10)
        // This should trigger the `return 0;` line in bisectLeft
        $mockHasher->setHashValue(5);
        $result = $consistentHash->lookup('resource');

        // When the hash is below all positions, it wraps around to the first target
        self::assertSame('t1', $result);
    }

    public function testConstructorThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ConsistentHash(replicas: 0);
    }

    public function testFallbackPrecedenceWhenServerRemoved(): void
    {
        $mockHasher     = new MockHasher(0);
        $consistentHash = new ConsistentHash($mockHasher, 1);

        $mockHasher->setHashValue(10);
        $consistentHash->addTarget('t1');

        $mockHasher->setHashValue(20);
        $consistentHash->addTarget('t2');

        $mockHasher->setHashValue(30);
        $consistentHash->addTarget('t3');

        $mockHasher->setHashValue(15);

        self::assertSame('t2', $consistentHash->lookup('resource'));
        self::assertSame(
            ['t2', 't3', 't1'],
            $consistentHash->lookupList('resource', 3),
        );

        $consistentHash->removeTarget('t2');

        self::assertSame('t3', $consistentHash->lookup('resource'));
        self::assertSame(
            ['t3', 't1'],
            $consistentHash->lookupList('resource', 3),
        );

        $consistentHash->removeTarget('t3');

        self::assertSame('t1', $consistentHash->lookup('resource'));
        self::assertSame(
            ['t1'],
            $consistentHash->lookupList('resource', 3),
        );
    }

    public function testGetAllTargetsEmpty(): void
    {
        $consistentHash = new ConsistentHash();
        self::assertSame([], $consistentHash->getAllTargets());
    }

    public function testGetMoreTargetsThanExist(): void
    {
        $consistentHash = new ConsistentHash();
        $consistentHash->addTarget('target1');
        $consistentHash->addTarget('target2');

        $targets = $consistentHash->lookupList('resource', 4);

        self::assertCount(2, $targets);
        self::assertNotSame($targets[0], $targets[1]);
    }

    public function testGetMultipleTargets(): void
    {
        $consistentHash = new ConsistentHash();

        foreach (range(1, 10) as $i) {
            $consistentHash->addTarget(\sprintf('target%s', $i));
        }

        $targets = $consistentHash->lookupList('resource', 2);

        self::assertCount(2, $targets);
        self::assertNotSame($targets[0], $targets[1]);
    }

    public function testGetMultipleTargetsNeedingToLoopToStart(): void
    {
        $mockHasher     = new MockHasher(0);
        $consistentHash = new ConsistentHash($mockHasher, 1);

        $mockHasher->setHashValue(10);
        $consistentHash->addTarget('t1');

        $mockHasher->setHashValue(20);
        $consistentHash->addTarget('t2');

        $mockHasher->setHashValue(30);
        $consistentHash->addTarget('t3');

        $mockHasher->setHashValue(40);
        $consistentHash->addTarget('t4');

        $mockHasher->setHashValue(50);
        $consistentHash->addTarget('t5');

        $mockHasher->setHashValue(35);
        $targets = $consistentHash->lookupList('resource', 4);

        self::assertSame(['t4', 't5', 't1', 't2'], $targets);
    }

    public function testGetMultipleTargetsWithOnlyOneTarget(): void
    {
        $consistentHash = new ConsistentHash();
        $consistentHash->addTarget('single-target');

        $targets = $consistentHash->lookupList('resource', 2);

        self::assertCount(1, $targets);
        self::assertSame('single-target', $targets[0]);
    }

    public function testGetMultipleTargetsWithoutGettingAnyBeforeLoopToStart(): void
    {
        $mockHasher     = new MockHasher(0);
        $consistentHash = new ConsistentHash($mockHasher, 1);

        $mockHasher->setHashValue(10);
        $consistentHash->addTarget('t1');

        $mockHasher->setHashValue(20);
        $consistentHash->addTarget('t2');

        $mockHasher->setHashValue(30);
        $consistentHash->addTarget('t3');

        $mockHasher->setHashValue(100);
        $targets = $consistentHash->lookupList('resource', 2);

        self::assertSame(['t1', 't2'], $targets);
    }

    public function testGetMultipleTargetsWithoutNeedingToLoopToStart(): void
    {
        $mockHasher     = new MockHasher(0);
        $consistentHash = new ConsistentHash($mockHasher, 1);

        $mockHasher->setHashValue(10);
        $consistentHash->addTarget('t1');

        $mockHasher->setHashValue(20);
        $consistentHash->addTarget('t2');

        $mockHasher->setHashValue(30);
        $consistentHash->addTarget('t3');

        $mockHasher->setHashValue(15);
        $targets = $consistentHash->lookupList('resource', 2);

        self::assertSame(['t2', 't3'], $targets);
    }

    public function testHashSpaceConsistentLookupsAfterAddingAndRemoving(): void
    {
        $consistentHash = new ConsistentHash();

        foreach (range(1, 10) as $i) {
            $consistentHash->addTarget(\sprintf('target%s', $i));
        }

        $results1 = [];

        foreach (range(1, 100) as $i) {
            $results1[] = $consistentHash->lookup(\sprintf('t%s', $i));
        }

        $consistentHash->addTarget('new-target');
        $consistentHash->removeTarget('new-target');
        $consistentHash->addTarget('new-target');
        $consistentHash->removeTarget('new-target');

        $results2 = [];

        foreach (range(1, 100) as $i) {
            $results2[] = $consistentHash->lookup(\sprintf('t%s', $i));
        }

        // This is probably optimistic, as adding/removing a target may
        // clobber existing targets and is not expected to restore them.
        self::assertSame($results1, $results2);
    }

    public function testHashSpaceConsistentLookupsWithNewInstance(): void
    {
        $hashSpace1 = new ConsistentHash();

        foreach (range(1, 10) as $i) {
            $hashSpace1->addTarget(\sprintf('target%s', $i));
        }

        $results1 = [];

        foreach (range(1, 100) as $i) {
            $results1[] = $hashSpace1->lookup(\sprintf('t%s', $i));
        }

        $hashSpace2 = new ConsistentHash();

        foreach (range(1, 10) as $i) {
            $hashSpace2->addTarget(\sprintf('target%s', $i));
        }

        $results2 = [];

        foreach (range(1, 100) as $i) {
            $results2[] = $hashSpace2->lookup(\sprintf('t%s', $i));
        }

        self::assertSame($results1, $results2);
    }

    public function testHashSpaceLookupListEmpty(): void
    {
        $consistentHash = new ConsistentHash();

        self::assertEmpty($consistentHash->lookupList('t1', 2));
    }

    public function testHashSpaceLookupListNoTargets(): void
    {
        $this->expectException(TargetException::class);
        $this->expectExceptionMessage('No targets exist');
        $consistentHash = new ConsistentHash();
        $consistentHash->lookup('t1');
    }

    public function testHashSpaceLookupsAreValidTargets(): void
    {
        $targets = [];

        foreach (range(1, 10) as $i) {
            $targets[] = \sprintf('target%s', $i);
        }

        $consistentHash = new ConsistentHash();
        $consistentHash->addTargets($targets);

        foreach (range(1, 10) as $i) {
            self::assertContains(
                $consistentHash->lookup(\sprintf('r%s', $i)),
                $targets,
                'target must be in list of targets',
            );
        }
    }

    public function testHashSpaceRepeatableLookups(): void
    {
        $consistentHash = new ConsistentHash();

        foreach (range(1, 10) as $i) {
            $consistentHash->addTarget(\sprintf('target%s', $i));
        }

        self::assertSame($consistentHash->lookup('t1'), $consistentHash->lookup('t1'));
        self::assertSame($consistentHash->lookup('t2'), $consistentHash->lookup('t2'));
    }

    public function testRemoveTarget(): void
    {
        $consistentHash = new ConsistentHash();
        $consistentHash->addTarget('t-a');
        $consistentHash->addTarget('t-b');
        $consistentHash->addTarget('t-c');
        $consistentHash->removeTarget('t-b');

        self::assertSame(['t-a', 't-c'], $consistentHash->getAllTargets());
    }

    public function testRemoveTargetFailsOnMissingTarget(): void
    {
        $consistentHash = new ConsistentHash();
        $this->expectException(TargetException::class);
        $consistentHash->removeTarget('not-there');
    }
}
