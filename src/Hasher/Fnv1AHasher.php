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
 * Uses Fnv1-a to hash a value into a 32bit int.
 *
 * Note: specifically uses the 'fnv1a32' algo in PHP's hash().
 *
 * @todo Look into fnv1a64
 *
 * @see https://en.wikipedia.org/wiki/Fowler%E2%80%93Noll%E2%80%93Vo_hash_function
 */
class Fnv1AHasher implements HasherInterface
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
        return (int) hexdec(substr(hash('fnv1a32', $string), 0, 8));
    }
}
