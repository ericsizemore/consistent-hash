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

use Esi\ConsistentHash\Hasher\Crc32Hasher;
use Esi\ConsistentHash\Hasher\Md5Hasher;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(Crc32Hasher::class)]
#[CoversClass(Md5Hasher::class)]
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

    public function testMd5Hash(): void
    {
        $hasher  = new Md5Hasher();
        $result1 = $hasher->hash('test');
        $result2 = $hasher->hash('test');
        $result3 = $hasher->hash('different');

        self::assertEquals($result1, $result2);
        self::assertNotEquals($result1, $result3); // fragile but worthwhile
    }
}
