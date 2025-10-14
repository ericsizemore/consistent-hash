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

use Esi\ConsistentHash\Hasher\HasherInterface;
use Override;

/**
 * @internal
 */
final class MockHasher implements HasherInterface
{
    public function __construct(private int $hashValue) {}

    #[Override]
    public function hash(string $string): int
    {
        return $this->hashValue;
    }

    public function setHashValue(int $hash): void
    {
        $this->hashValue = $hash;
    }
}
