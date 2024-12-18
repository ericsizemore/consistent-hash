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
#[UsesClass(Crc32Hasher::class)]
#[Group('default')]
class ConsistentHashTest extends TestCase
{
    public function testAddTargetAndGetAllTargets(): void
    {
        $hashSpace = new ConsistentHash();
        $hashSpace->addTarget('t-a');
        $hashSpace->addTarget('t-b');
        $hashSpace->addTarget('t-c');

        self::assertSame(['t-a', 't-b', 't-c'], $hashSpace->getAllTargets());
    }

    public function testAddTargetsAndGetAllTargets(): void
    {
        $targets = ['t-a', 't-b', 't-c'];

        $hashSpace = new ConsistentHash();
        $hashSpace->addTargets($targets);
        self::assertSame($hashSpace->getAllTargets(), $targets);
    }

    public function testAddTargetThrowsExceptionOnDuplicateTarget(): void
    {
        $hashSpace = new ConsistentHash();
        $hashSpace->addTarget('t-a');
        $this->expectException(TargetException::class);
        $hashSpace->addTarget('t-a');
    }

    public function testFallbackPrecedenceWhenServerRemoved(): void
    {
        $mockHasher = new MockHasher(0);
        $hashSpace  = new ConsistentHash($mockHasher, 1);

        $mockHasher->setHashValue(10);
        $hashSpace->addTarget('t1');

        $mockHasher->setHashValue(20);
        $hashSpace->addTarget('t2');

        $mockHasher->setHashValue(30);
        $hashSpace->addTarget('t3');

        $mockHasher->setHashValue(15);

        self::assertSame('t2', $hashSpace->lookup('resource'));
        self::assertSame(
            ['t2', 't3', 't1'],
            $hashSpace->lookupList('resource', 3),
        );

        $hashSpace->removeTarget('t2');

        self::assertSame('t3', $hashSpace->lookup('resource'));
        self::assertSame(
            ['t3', 't1'],
            $hashSpace->lookupList('resource', 3),
        );

        $hashSpace->removeTarget('t3');

        self::assertSame('t1', $hashSpace->lookup('resource'));
        self::assertSame(
            ['t1'],
            $hashSpace->lookupList('resource', 3),
        );
    }
    public function testGetAllTargetsEmpty(): void
    {
        $hashSpace = new ConsistentHash();
        self::assertSame([], $hashSpace->getAllTargets());
    }

    public function testGetMoreTargetsThanExist(): void
    {
        $hashSpace = new ConsistentHash();
        $hashSpace->addTarget('target1');
        $hashSpace->addTarget('target2');

        $targets = $hashSpace->lookupList('resource', 4);

        self::assertCount(2, $targets);
        self::assertNotEquals($targets[0], $targets[1]);
    }

    public function testGetMultipleTargets(): void
    {
        $hashSpace = new ConsistentHash();
        foreach (range(1, 10) as $i) {
            $hashSpace->addTarget(\sprintf('target%s', $i));
        }

        $targets = $hashSpace->lookupList('resource', 2);

        self::assertCount(2, $targets);
        self::assertNotEquals($targets[0], $targets[1]);
    }

    public function testGetMultipleTargetsNeedingToLoopToStart(): void
    {
        $mockHasher = new MockHasher(0);
        $hashSpace  = new ConsistentHash($mockHasher, 1);

        $mockHasher->setHashValue(10);
        $hashSpace->addTarget('t1');

        $mockHasher->setHashValue(20);
        $hashSpace->addTarget('t2');

        $mockHasher->setHashValue(30);
        $hashSpace->addTarget('t3');

        $mockHasher->setHashValue(40);
        $hashSpace->addTarget('t4');

        $mockHasher->setHashValue(50);
        $hashSpace->addTarget('t5');

        $mockHasher->setHashValue(35);
        $targets = $hashSpace->lookupList('resource', 4);

        self::assertSame(['t4', 't5', 't1', 't2'], $targets);
    }

    public function testGetMultipleTargetsWithOnlyOneTarget(): void
    {
        $hashSpace = new ConsistentHash();
        $hashSpace->addTarget('single-target');

        $targets = $hashSpace->lookupList('resource', 2);

        self::assertCount(1, $targets);
        self::assertSame('single-target', $targets[0]);
    }

    public function testGetMultipleTargetsWithoutGettingAnyBeforeLoopToStart(): void
    {
        $mockHasher = new MockHasher(0);
        $hashSpace  = new ConsistentHash($mockHasher, 1);

        $mockHasher->setHashValue(10);
        $hashSpace->addTarget('t1');

        $mockHasher->setHashValue(20);
        $hashSpace->addTarget('t2');

        $mockHasher->setHashValue(30);
        $hashSpace->addTarget('t3');

        $mockHasher->setHashValue(100);
        $targets = $hashSpace->lookupList('resource', 2);

        self::assertSame(['t1', 't2'], $targets);
    }

    public function testGetMultipleTargetsWithoutNeedingToLoopToStart(): void
    {
        $mockHasher = new MockHasher(0);
        $hashSpace  = new ConsistentHash($mockHasher, 1);

        $mockHasher->setHashValue(10);
        $hashSpace->addTarget('t1');

        $mockHasher->setHashValue(20);
        $hashSpace->addTarget('t2');

        $mockHasher->setHashValue(30);
        $hashSpace->addTarget('t3');

        $mockHasher->setHashValue(15);
        $targets = $hashSpace->lookupList('resource', 2);

        self::assertSame(['t2', 't3'], $targets);
    }

    public function testHashSpaceConsistentLookupsAfterAddingAndRemoving(): void
    {
        $hashSpace = new ConsistentHash();
        foreach (range(1, 10) as $i) {
            $hashSpace->addTarget(\sprintf('target%s', $i));
        }

        $results1 = [];
        foreach (range(1, 100) as $i) {
            $results1[] = $hashSpace->lookup(\sprintf('t%s', $i));
        }

        $hashSpace->addTarget('new-target');
        $hashSpace->removeTarget('new-target');
        $hashSpace->addTarget('new-target');
        $hashSpace->removeTarget('new-target');

        $results2 = [];
        foreach (range(1, 100) as $i) {
            $results2[] = $hashSpace->lookup(\sprintf('t%s', $i));
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
        $hashSpace = new ConsistentHash();
        self::assertEmpty($hashSpace->lookupList('t1', 2));
    }

    public function testHashSpaceLookupListNoTargets(): void
    {
        $this->expectException(TargetException::class);
        $this->expectExceptionMessage('No targets exist');
        $hashSpace = new ConsistentHash();
        $hashSpace->lookup('t1');
    }

    public function testHashSpaceLookupsAreValidTargets(): void
    {
        $targets = [];
        foreach (range(1, 10) as $i) {
            $targets[] = \sprintf('target%s', $i);
        }

        $hashSpace = new ConsistentHash();
        $hashSpace->addTargets($targets);

        foreach (range(1, 10) as $i) {
            self::assertTrue(
                \in_array($hashSpace->lookup(\sprintf('r%s', $i)), $targets, true),
                'target must be in list of targets',
            );
        }
    }

    public function testHashSpaceRepeatableLookups(): void
    {
        $hashSpace = new ConsistentHash();
        foreach (range(1, 10) as $i) {
            $hashSpace->addTarget(\sprintf('target%s', $i));
        }

        self::assertSame($hashSpace->lookup('t1'), $hashSpace->lookup('t1'));
        self::assertSame($hashSpace->lookup('t2'), $hashSpace->lookup('t2'));
    }

    public function testRemoveTarget(): void
    {
        $hashSpace = new ConsistentHash();
        $hashSpace->addTarget('t-a');
        $hashSpace->addTarget('t-b');
        $hashSpace->addTarget('t-c');
        $hashSpace->removeTarget('t-b');
        self::assertSame(['t-a', 't-c'], $hashSpace->getAllTargets());
    }

    public function testRemoveTargetFailsOnMissingTarget(): void
    {
        $hashSpace = new ConsistentHash();
        $this->expectException(TargetException::class);
        $hashSpace->removeTarget('not-there');
    }
}
