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

namespace Esi\ConsistentHash\Hasher;

/**
 * Uses XXH32 to hash a value into a 32bit int.
 *
 * @todo Look into xxh64 (64-bit hash), xxh3 (64-bit hash), and xxh128 (128-bit hash)
 *
 * @see https://xxhash.com/
 */
class Xxh32Hasher implements HasherInterface
{
    /**
     * {@inheritDoc}
     *
     * 8 hexits = 32bit, which also allows us to forego having to check whether
     * it is over PHP_INT_MAX.
     *
     * The substring is converted to an int since hex strings sometimes get
     * treated as ints if all digits are ints and this results in unexpected
     * sorting order.
     */
    public function hash(string $string): int
    {
        return (int) hexdec(substr(hash('xxh32', $string), 0, 8));
    }
}
